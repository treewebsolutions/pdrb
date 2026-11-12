<?php

namespace frontend\models;

use common\models\Language;
use common\models\Member;
use common\models\MemberTranslation;
use common\models\Membership;
use common\models\PaymentMetadata;
use common\models\Template;
use tws\helpers\Url;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class MembershipForm extends Member
{
	/** @internal Stripe Checkout — Bancontact */
	const CHECKOUT_PAYMENT_BANCONTACT = 'bancontact';
	/** @internal Stripe Checkout — Visa / Mastercard etc. */
	const CHECKOUT_PAYMENT_CARD = 'card';

	/**
	 * How the user pays in Stripe Checkout (only for fee-based member types).
	 *
	 * @var string|null
	 */
	public $checkoutPaymentMethod;

	/**
	 * @var array The multilingual role of the member (set programmatically, not a form field).
	 */
	public $role = [];

	/**
	 * @var array The multilingual content of the member.
	 */
	public $content = [];

	/**
	 * @var string The honeypot field.
	 */
	public $backUrl;

	/**
	 * @var string The honeypot field.
	 */
	public $workEmail;

	/**
	 * @var string The honeypot field.
	 */
	public $captchaResponse;

	/**
	 * @var Membership|null The membership instance created for paid member types.
	 */
	private $_membership;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->status = static::STATUS_INACTIVE;
		// Do not set default type - let controller decide based on URL parameter
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$paidMemberTypeIdsJson = Json::encode(array_values(array_map('intval', array_keys(static::getAnnualFees()))));
		$membershipTypeSelect = '#'
			. mb_strtolower(StringHelper::basename(static::class))
			. '-type';

		return ArrayHelper::merge(parent::rules(), [
			[['email', 'phone', 'gender', 'address', 'locality', 'country', 'type'], 'required'],
			[['content'], 'each', 'rule' => ['trim']],
			[['content'], 'each', 'rule' => ['default', 'value' => null]],
			[['address', 'locality', 'country'], 'trim'],
			[['address', 'locality', 'country'], 'string', 'max' => 255],
			[['type'], 'integer'],
			['type', 'validateSuperadminTestType'],
			[['checkoutPaymentMethod'], 'trim'],
			[['checkoutPaymentMethod'], 'required',
				'when' => function (self $model) {
					return $model->getRequiresPayment();
				},
				'whenClient' => "function (attribute, value) {\n"
					. "var paid = {$paidMemberTypeIdsJson};\n"
					. "var v = parseInt(jQuery('{$membershipTypeSelect}').val(), 10);\n"
					. "return paid.indexOf(v) >= 0;\n"
					. '}',
				'message' => Yii::t('frontend', 'Please select a payment method (Visa / Mastercard or Bancontact).'),
			],
			[['checkoutPaymentMethod'], 'in',
				'range' => [self::CHECKOUT_PAYMENT_BANCONTACT, self::CHECKOUT_PAYMENT_CARD],
				'skipOnEmpty' => true,
				'message' => Yii::t('frontend', 'Payment method must be Visa / Mastercard or Bancontact.'),
			],
			['workEmail', 'safe'],
			['backUrl', 'safe'],
			['captchaResponse', 'safe'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'content' => Yii::t('label', 'Content'),
			'checkoutPaymentMethod' => Yii::t('frontend', 'Payment method'),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return ArrayHelper::merge(parent::attributes(), [
			'content',
			'checkoutPaymentMethod',
			'workEmail',
			'backUrl',
			'captchaResponse',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return Model::scenarios();
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		parent::afterFind();

		$this->content = ArrayHelper::map($this->memberTranslations, 'language_id', 'content');
	}

	/**
	 * Annual fees for this form: normal tiers plus 1 EUR test tier when the user is superAdmin.
	 *
	 * @return array<int, int>
	 */
	public static function getAnnualFees()
	{
		$fees = parent::getAnnualFees();
		if (static::isCurrentUserSuperAdmin()) {
			$fees += Member::getSuperadminTestAnnualFees();
		}

		return $fees;
	}

	/**
	 * Whether the logged-in user has the superAdmin role (RBAC).
	 */
	public static function isCurrentUserSuperAdmin(): bool
	{
		if (Yii::$app->user->isGuest) {
			return false;
		}
		$roles = Yii::$app->authManager->getRolesByUser((string) Yii::$app->user->id);

		return isset($roles['superAdmin']);
	}

	/**
	 * Rejects superadmin test tier if the submitter is not superAdmin.
	 *
	 * @param string $attribute
	 */
	public function validateSuperadminTestType($attribute)
	{
		if ($this->hasErrors()) {
			return;
		}
		if ((int) $this->type === Member::TYPE_SUPERADMIN_TEST && !static::isCurrentUserSuperAdmin()) {
			$this->addError($attribute, Yii::t('frontend', 'This membership type is not available.'));
		}
	}

	/**
	 * Indicates whether the chosen member type requires payment.
	 *
	 * @return bool
	 */
	public function getRequiresPayment()
	{
		return array_key_exists($this->type, static::getAnnualFees());
	}

	/**
	 * Gets the Membership model created during save (only for paid member types).
	 *
	 * @return Membership|null
	 */
	public function getMembership()
	{
		return $this->_membership;
	}

	/**
	 * Saves the translations.
	 *
	 * @return bool
	 */
	protected function saveMemberTranslations()
	{
		try {
			foreach (Language::findAllLanguages() as $language) {
				$memberTranslation = MemberTranslation::findOne([
					'member_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$memberTranslation) {
					$memberTranslation = new MemberTranslation();
					$memberTranslation->language_id = $language->language_id;
				}

				// Set role from type message if available (set in controller)
				if (!empty($this->role) && isset($this->role[$language->language_id])) {
					$memberTranslation->role = \common\helpers\StringHelper::truncate($this->role[$language->language_id], 240, '');
					$memberTranslation->slug = \common\helpers\Inflector::slug($this->fullName) ?: null;
				}

				$memberTranslation->content = $this->content[$language->language_id] ?? null;

				if ($memberTranslation->role) {
					$this->link('memberTranslations', $memberTranslation);
				}
			}

			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * Creates a Membership record for paid member types.
	 *
	 * @return bool
	 */
	protected function saveMembership()
	{
		try {
			$fees = static::getAnnualFees();
			if (!isset($fees[$this->type])) {
				return true;
			}

			$membership = new Membership();
			$membership->detachBehavior('BlameableBehavior');
			$membership->member_id = $this->id;
			$membership->code = $this->generateMembershipCode();
			$membership->price = $fees[$this->type];
			$membership->currency = 'EUR';
			$membership->type = $this->type;
			$membership->status = Membership::STATUS_PENDING;
			$validFrom = new \DateTime();
			$membership->start_at = $validFrom->format('Y-m-d H:i:s');
			$membership->end_at = (clone $validFrom)->modify('+1 year')->format('Y-m-d H:i:s');
			if (!$membership->save()) {
				throw new \Exception('Membership cannot be saved.');
			}

			$paymentMetadata = new PaymentMetadata();
			$paymentMetadata->detachBehavior('BlameableBehavior');
			$paymentMetadata->membership_id = $membership->id;
			$paymentMetadata->payment_method = $this->checkoutPaymentMethod === self::CHECKOUT_PAYMENT_BANCONTACT
				? PaymentMetadata::PAYMENT_METHOD_BANCONTACT
				: PaymentMetadata::PAYMENT_METHOD_CARD;
			$paymentMetadata->payment_processor = PaymentMetadata::PAYMENT_PROCESSOR_STRIPE;
			$paymentMetadata->recurring_payment = PaymentMetadata::NO;
			$paymentMetadata->ip_address = Yii::$app->request->userIP;
			$paymentMetadata->status = PaymentMetadata::STATUS_ACTIVE;
			if (!$paymentMetadata->save()) {
				throw new \Exception('Payment metadata cannot be saved.');
			}

			$this->_membership = $membership;
			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * Generates a unique membership code.
	 *
	 * @return string
	 */
	protected function generateMembershipCode()
	{
		do {
			$code = sprintf(
				'MBR-%s-%s-%s',
				date('Y'),
				str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
				strtoupper(Yii::$app->security->generateRandomString(4))
			);
		} while (Membership::find()->where(['code' => $code])->exists());

		return $code;
	}

	/**
	 * Builds a Stripe Checkout session (hosted page): card or Bancontact only, based on saved payment metadata.
	 *
	 * @return string|null
	 */
	public function createStripeCheckoutUrl()
	{
		try {
			$membership = $this->getMembership();
			if (!$membership) {
				return null;
			}

			$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
			$stripe = new \Stripe\StripeClient($payment->privateKey);

			$resultUrl = Url::to(['/payment/default/result'], true);

			$paymentMetadata = $membership->paymentMetadata;
			$paymentMethodTypes = ($paymentMetadata && (int) $paymentMetadata->payment_method === PaymentMetadata::PAYMENT_METHOD_BANCONTACT)
				? ['bancontact']
				: ['card'];

			$productName = $this->getMembershipDisplayName($membership);

			$session = $stripe->checkout->sessions->create([
				'metadata' => [
					'membership_id' => $membership->id,
					'ip' => Yii::$app->request->userIP,
				],
				'locale' => mb_substr(Yii::$app->language, 0, 2),
				'success_url' => $resultUrl . '?session_id={CHECKOUT_SESSION_ID}',
				'cancel_url' => $resultUrl . '?cancel=1',
				'mode' => 'payment',
				'payment_method_types' => $paymentMethodTypes,
				'customer_email' => $this->email,
				'payment_intent_data' => [
					'description' => $productName,
				],
				'line_items' => [
					[
						'price_data' => [
							'currency' => 'eur',
							'product_data' => [
								'name' => $productName,
							],
							'unit_amount' => (int) round((float) $membership->price * 100),
						],
						'quantity' => 1,
					],
				],
			]);

			return $session->url;
		} catch (\Exception $e) {
			$this->addError('', $e->getMessage());
			return null;
		}
	}

	/**
	 * Builds a human readable display name for the membership product.
	 *
	 * @param Membership $membership
	 * @return string
	 */
	protected function getMembershipDisplayName(Membership $membership)
	{
		$typeLabels = static::getTypeLabels();
		$typeId = $membership->type ?? $this->type;

		return implode(' - ', array_filter([
			Yii::t('label', 'Membership'),
			$typeLabels[$typeId] ?? null,
			$membership->getPeriodDescription(),
			$this->fullName,
		]));
	}

	/**
	 * Sends membership welcome email.
	 *
	 * @return bool
	 */
	protected function sendMembershipWelcomeEmail()
	{
		try {
			$template = Template::findDefaultByTypeAndVariant(Template::TYPE_EMAIL, Template::EMAIL_VARIANT_MEMBERSHIP_WELCOME);
			if (!$template || !($templateTranslation = $template->getTranslation())) {
				return false;
			}
			$typeLabels = static::getTypeLabels();
			$fees = static::getAnnualFees();
			$currency = Yii::$app->formatter->currencyCode;
			$shortCodeValues = [
				'{{APP_NAME}}' => Yii::$app->name,
				'{{APP_URL}}' => Url::to(['/site/index'], true, '@frontend'),
				'{{APP_LOGO_URL}}' => Url::to('@uploads/' . Yii::$app->settings->get('appLogo'), true) ?: Url::to('@frontend/web/img/logo.png', true),
				'{{APP_LOGO_ALT_URL}}' => Url::to('@uploads/' . Yii::$app->settings->get('appLogoAlt'), true) ?: Url::to('@frontend/web/img/logo-alt.png', true),
				'{{FIRST_NAME}}' => $this->first_name,
				'{{MIDDLE_NAME}}' => $this->middle_name,
				'{{LAST_NAME}}' => $this->last_name,
				'{{EMAIL}}' => $this->email,
				'{{PHONE}}' => $this->phone,
				'{{MEMBERSHIP_TYPE}}' => $typeLabels[$this->type] ?? '',
				'{{ANNUAL_FEE}}' => isset($fees[$this->type]) ? $fees[$this->type] . ' ' . $currency : '',
			];
			return Yii::$app->mailer->compose()
				->setTo([$this->email => $this->fullName])
				->setSubject(strtr($templateTranslation->subject, $shortCodeValues))
				->setHtmlBody(strtr($templateTranslation->content, $shortCodeValues))
				->send();
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @throws \Throwable
	 */
	public function save($runValidation = true, $attributeNames = null)
	{
		if (!empty($this->workEmail)) {
			return false;
		}
		unset($this->workEmail);
		if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')) {
			if (!empty($this->captchaResponse)) {
				$result = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . Yii::$app->settings->get('reCaptchaSecretKey', 'general') . '&response=' . $this->captchaResponse);
				$response = json_decode($result);
				if (empty($response->success)) {
					return false;
				}
			}
		}
		unset($this->captchaResponse);

		$transaction = static::getDb()->beginTransaction();
		try {
			$this->detachBehavior('BlameableBehavior');
			if (!parent::save($runValidation, $attributeNames)) {
				throw new \Exception();
			}
			if (!$this->saveMemberTranslations()) {
				throw new \Exception();
			}
			if (!$this->saveMembership()) {
				throw new \Exception();
			}
			// Welcome email is only sent immediately for free member types.
			// For paid types the email is sent after the payment is confirmed by PaymentResult.
			if (!$this->getRequiresPayment()) {
				if (!$this->sendMembershipWelcomeEmail()) {
					throw new \Exception();
				}
			}
			$transaction->commit();
			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			$transaction->rollBack();
			return false;
		}
	}
}

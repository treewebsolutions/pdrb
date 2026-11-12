<?php

namespace frontend\models;

use common\models\PaymentMetadata;
use tws\helpers\Url;
use Yii;
use yii\base\Model;

/**
 * Public donation form: Stripe Checkout (card or Bancontact), no DB row before payment.
 */
class DonationForm extends Model
{
	public const CHECKOUT_PAYMENT_BANCONTACT = 'bancontact';
	public const CHECKOUT_PAYMENT_CARD = 'card';

	public const AMOUNT_MAX = 500;
	public const AMOUNT_STEP = 10;

	/** @var string */
	public $first_name;
	/** @var string */
	public $last_name;
	/** @var string|null */
	public $middle_name;
	/** @var string */
	public $email;
	/** @var string */
	public $phone;
	/** @var int|null Amount in EUR (integer) */
	public $amount;
	/** @var string */
	public $checkoutPaymentMethod;

	/** @var string Honeypot */
	public $workEmail;
	/** @var string|null */
	public $captchaResponse;

	/**
	 * Minimum donation in EUR: 1 for superAdmin, 100 otherwise.
	 */
	public function getAmountMin(): int
	{
		return MembershipForm::isCurrentUserSuperAdmin() ? 1 : 100;
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->amount === null || $this->amount === '') {
			$this->amount = $this->getAmountMin();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$amountMin = $this->getAmountMin();

		return [
			[
				'amount',
				'default',
				'value' => function (self $model, $attribute) {
					return $model->getAmountMin();
				},
			],
			[['first_name', 'last_name', 'email', 'phone', 'amount', 'checkoutPaymentMethod'], 'required'],
			[['first_name', 'last_name', 'middle_name', 'email', 'phone'], 'trim'],
			[['first_name', 'last_name', 'middle_name', 'email', 'phone'], 'string', 'max' => 255],
			['email', 'email'],
			[
				'amount',
				'number',
				'integerOnly' => true,
				'min' => $amountMin,
				'max' => self::AMOUNT_MAX,
				'tooSmall' => Yii::t('frontend', 'The amount must be at least {min} EUR.', ['min' => $amountMin]),
				'tooBig' => Yii::t('frontend', 'The amount may not exceed {max} EUR.', ['max' => self::AMOUNT_MAX]),
			],
			['amount', 'validateAmountStep'],
			[['checkoutPaymentMethod'], 'in', 'range' => [self::CHECKOUT_PAYMENT_BANCONTACT, self::CHECKOUT_PAYMENT_CARD]],
			['workEmail', 'safe'],
			['captchaResponse', 'safe'],
		];
	}

	/**
	 * Ensures amount is min + n×step up to max (same step for all users).
	 *
	 * @param string $attribute
	 */
	public function validateAmountStep($attribute)
	{
		if ($this->hasErrors($attribute)) {
			return;
		}
		$min = $this->getAmountMin();
		$v = (int) $this->$attribute;
		if (($v - $min) % self::AMOUNT_STEP !== 0) {
			$this->addError($attribute, Yii::t('frontend', 'The amount must be in steps of {step} EUR from {min} EUR to {max} EUR.', [
				'step' => self::AMOUNT_STEP,
				'min' => $min,
				'max' => self::AMOUNT_MAX,
			]));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'first_name' => Yii::t('label', 'First Name'),
			'last_name' => Yii::t('label', 'Last Name'),
			'middle_name' => Yii::t('label', 'Middle Name'),
			'email' => Yii::t('label', 'Email'),
			'phone' => Yii::t('label', 'Phone'),
			'amount' => Yii::t('frontend', 'Donation amount'),
			'checkoutPaymentMethod' => Yii::t('frontend', 'Payment method'),
		];
	}

	/**
	 * Honeypot + optional reCAPTCHA; run after validate().
	 *
	 * @return bool
	 */
	public function validateAntiSpam(): bool
	{
		if (!empty($this->workEmail)) {
			return false;
		}
		if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')) {
			if (!empty($this->captchaResponse)) {
				$result = @file_get_contents(
					'https://www.google.com/recaptcha/api/siteverify?secret='
					. Yii::$app->settings->get('reCaptchaSecretKey', 'general')
					. '&response=' . $this->captchaResponse
				);
				$response = json_decode($result ?: '');
				if (empty($response->success)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Stripe line item / PaymentIntent label (donation + donor name).
	 * Uses the payment module PhpMessageSource so strings are translated per locale.
	 *
	 * @return string
	 */
	protected function getStripeDonationLineDescription(): string
	{
		$name = trim(implode(' ', array_filter([
			$this->first_name,
			$this->middle_name,
			$this->last_name,
		])));

		return implode(' - ', array_filter([
			Yii::t('payment', 'Donation'),
			$name !== '' ? $name : null,
		]));
	}

	/**
	 * Stripe Checkout session URL for this donation.
	 *
	 * @return string|null
	 */
	public function createStripeCheckoutUrl(): ?string
	{
		try {
			$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
			$stripe = new \Stripe\StripeClient($payment->privateKey);

			$resultUrl = Url::to(['/payment/default/result'], true);
			$paymentMethodTypes = $this->checkoutPaymentMethod === self::CHECKOUT_PAYMENT_BANCONTACT
				? ['bancontact']
				: ['card'];

			$ref = Yii::$app->security->generateRandomString(24);
			$lineDescription = $this->getStripeDonationLineDescription();

			$session = $stripe->checkout->sessions->create([
				'metadata' => [
					'donation' => '1',
					'donation_ref' => $ref,
					'amount_eur' => (string) (int) $this->amount,
					'ip' => Yii::$app->request->userIP,
				],
				'locale' => mb_substr(Yii::$app->language, 0, 2),
				'success_url' => $resultUrl . '?session_id={CHECKOUT_SESSION_ID}',
				'cancel_url' => $resultUrl . '?cancel=1',
				'mode' => 'payment',
				'payment_method_types' => $paymentMethodTypes,
				'customer_email' => $this->email,
				'payment_intent_data' => [
					'description' => $lineDescription,
				],
				'line_items' => [
					[
						'price_data' => [
							'currency' => 'eur',
							'product_data' => [
								'name' => $lineDescription,
								'description' => $lineDescription,
							],
							'unit_amount' => (int) round((float) $this->amount * 100),
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
}

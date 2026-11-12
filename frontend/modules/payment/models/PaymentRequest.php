<?php

namespace frontend\modules\payment\models;

use common\models\Member;
use common\models\Membership;
use common\models\PaymentMetadata;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use tws\helpers\Url;

class PaymentRequest extends Model
{
	/**
	 * @var int The Membership model ID.
	 */
	public $membership_id;

	/**
	 * @var string|int The payment processor to be used.
	 */
	public $payment_processor;

	/**
	 * @var string The email address to be used for payment.
	 */
	public $email;

	/**
	 * @var string The payment processor authorization token.
	 */
	public $token;

	/**
	 * @var int The payment type.
	 */
	public $type;

	/**
	 * @var User The User model.
	 */
	private $_user;

	/**
	 * @var Membership The Membership model.
	 */
	private $_membership;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->type = PaymentMetadata::PAYMENT_TYPE_MEMBERSHIP;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['membership_id', 'payment_processor'], 'required'],
			['membership_id', function ($attribute) {
				if (!$this->getMembership()) {
					$this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
				}
			}],
			[['payment_processor'], 'in', 'range' => array_keys(PaymentMetadata::getPaymentProcessorLabels())],
			[['email', 'token'], 'string'],
			[['type'], 'integer'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'membership_id' => Yii::t('label', 'Membership'),
			'payment_processor' => Yii::t('label', 'Payment Processor'),
		]);
	}

	/**
	 * Gets the User model.
	 *
	 * @return User|null
	 */
	public function getUser()
	{
		if (!$this->_user) {
			$this->_user = Yii::$app->user->identity;
		}
		return $this->_user;
	}

	/**
	 * Gets the Membership model.
	 *
	 * @return Membership|null
	 */
	public function getMembership()
	{
		if (!$this->_membership) {
			$user = $this->getUser();
			$query = Membership::find()
				->andWhere([
					'id' => $this->membership_id,
					'deleted' => Membership::NO,
				])
				->andWhere([
					'IN', 'status', [Membership::STATUS_INACTIVE, Membership::STATUS_PENDING, Membership::STATUS_EXPIRED],
				]);

			if ($user && $user->member_id) {
				$query->andWhere(['member_id' => $user->member_id]);
			}

			$this->_membership = $query->one();
		}
		return $this->_membership;
	}

	/**
	 * Builds Stripe Checkout Session.
	 *
	 * @return mixed
	 */
	public function buildStripeCheckout()
	{
		try {
			$payment = Yii::$app->payment->via($this->payment_processor);
			$membership = $this->getMembership();
			$member = $membership->member;

			$stripe = new \Stripe\StripeClient(
				$payment->privateKey
			);

			$resultUrl = Url::to(['/payment/default/result'], true);
			$productName = $this->getMembershipDisplayName($membership);

			$data = [
				'metadata' => [
					'membership_id' => $membership->id,
					'ip' => Yii::$app->request->userIP,
				],
				'locale' => mb_substr(Yii::$app->language, 0, 2),
				'success_url' => $resultUrl . '?session_id={CHECKOUT_SESSION_ID}',
				'cancel_url' => $resultUrl . '?cancel=1',
				'mode' => 'payment',
				'payment_intent_data' => [
					'description' => $productName,
				],
				'line_items' => [
					[
						'price_data' => [
							'currency' => mb_strtolower($membership->currency ?: Yii::$app->formatter->currencyCode),
							'product_data' => [
								'name' => $productName,
							],
							'unit_amount' => (int) round((float) $membership->price * 100),
						],
						'quantity' => 1,
					],
				],
			];

			if ($member && !empty($member->email)) {
				$customers = $stripe->customers->search([
					'query' => "email:'" . $member->email . "'",
					'limit' => 1,
				], ['stripe_version' => '2022-11-15']);
				if (!empty($customers->data[0]->id)) {
					$data['customer'] = $customers->data[0]->id;
				} else {
					$data['customer_email'] = $member->email;
				}
			}

			$session = $stripe->checkout->sessions->create($data);

			$content = [];
			$content[] = Html::beginTag('form', [
				'id' => 'payment-request-form',
				'accept-charset' => 'UTF-8',
				'action' => $session->url,
				'method' => 'GET',
			]);
			$content[] = Html::endTag('form');
			return implode("\n", $content);
		} catch (\Exception $e) {
			$this->addError('', $e->getMessage());
			return null;
		}
	}

	/**
	 * Gets the payment request form.
	 *
	 * @return mixed
	 */
	public function getPaymentForm()
	{
		if ($this->payment_processor == PaymentMetadata::PAYMENT_PROCESSOR_STRIPE) {
			return $this->buildStripeCheckout();
		}
		return null;
	}

	/**
	 * Builds a human readable display name for the membership product.
	 *
	 * @param Membership $membership
	 * @return string
	 */
	protected function getMembershipDisplayName(Membership $membership)
	{
		$typeLabels = Member::getTypeLabels();
		$typeId = $membership->type;
		if ($typeId === null && $membership->member) {
			$typeId = $membership->member->type;
		}

		return implode(' - ', array_filter([
			Yii::t('label', 'Membership'),
			$typeId !== null ? ($typeLabels[$typeId] ?? null) : null,
			$membership->getPeriodDescription(),
			$membership->member ? $membership->member->fullName : null,
		]));
	}
}

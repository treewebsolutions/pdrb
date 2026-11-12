<?php

namespace frontend\modules\payment\models;

use common\models\Member;
use common\models\Membership;
use common\models\PaymentMetadata;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class PaymentMembershipForm extends Model
{
	/**
	 * @var int|string The Membership model ID.
	 */
	public $membership_id;

	/**
	 * @var int The payment method.
	 */
	public $payment_method;

	/**
	 * @var int The payment processor.
	 */
	public $payment_processor;

	/**
	 * @var bool Flag that indicates if the payer agrees with the automatic recurring payment.
	 */
	public $enable_recurring_payment;

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

		$this->payment_method = PaymentMetadata::PAYMENT_METHOD_CARD;
		$this->payment_processor = PaymentMetadata::PAYMENT_PROCESSOR_STRIPE;
		$this->enable_recurring_payment = Yii::$app->settings->get('enableRecurringPayment', 'payment');
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['membership_id', 'payment_method', 'payment_processor'], 'required'],
			[['payment_method', 'payment_processor'], 'integer'],
			[['enable_recurring_payment'], 'boolean'],
			['membership_id', function ($attribute) {
				if (!$this->getMembership()) {
					$this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
				}
			}],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'membership_id' => Yii::t('label', 'Membership'),
			'payment_method' => Yii::t('label', 'Payment Method'),
			'payment_processor' => Yii::t('label', 'Payment Processor'),
			'enable_recurring_payment' => Yii::t('label', 'Enable Recurring Payment'),
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
	 * Saves the PaymentMetadata model.
	 *
	 * @return bool
	 */
	protected function savePaymentMetadata()
	{
		try {
			$membership = $this->getMembership();

			if (!($paymentMetadata = $membership->paymentMetadata)) {
				$paymentMetadata = new PaymentMetadata();
				$paymentMetadata->membership_id = $membership->id;
			}
			$paymentMetadata->detachBehavior('BlameableBehavior');
			$paymentMetadata->payment_method = $this->payment_method;
			$paymentMetadata->payment_processor = $this->payment_processor;
			$paymentMetadata->recurring_payment = $this->enable_recurring_payment ? PaymentMetadata::YES : PaymentMetadata::NO;
			$paymentMetadata->ip_address = Yii::$app->request->userIP;
			$paymentMetadata->status = PaymentMetadata::STATUS_ACTIVE;
			if (!$paymentMetadata->save()) {
				throw new \Exception($paymentMetadata->getErrorSummary(false)[0]);
			}
			return true;
		} catch (\Exception $e) {
			$this->addError('', $e->getMessage());
			return false;
		}
	}

	/**
	 * Saves the models.
	 *
	 * @return bool
	 */
	public function save()
	{
		$dbTransaction = Yii::$app->db->beginTransaction();
		try {
			if (!$this->validate()) {
				throw new \Exception();
			}
			if (!$this->savePaymentMetadata()) {
				throw new \Exception();
			}
			$dbTransaction->commit();
			return true;
		} catch (\Exception $e) {
			$dbTransaction->rollBack();
			return false;
		}
	}
}

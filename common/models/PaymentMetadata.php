<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%payment_metadata}}".
 *
 * @property int $membership_id
 * @property string $payer_id
 * @property string $card_id
 * @property string $payment_id
 * @property int $payment_method
 * @property int $payment_processor
 * @property int $recurring_payment
 * @property string $ip_address
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property Membership $membership
 * @property User $creator
 * @property User $updater
 */
class PaymentMetadata extends CommonActiveRecord
{
	const PAYMENT_METHOD_CASH = 1;
	const PAYMENT_METHOD_BANK_TRANSFER = 2;
	const PAYMENT_METHOD_CARD = 3;
	const PAYMENT_METHOD_ONLINE = 4;
	const PAYMENT_METHOD_BANCONTACT = 5;

	const PAYMENT_PROCESSOR_STRIPE = 1;
	const PAYMENT_PROCESSOR_PAYPAL = 2;
	const PAYMENT_PROCESSOR_BANK = 3;

	const PAYMENT_TYPE_MEMBERSHIP = 1;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%payment_metadata}}';
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function behaviors()
	{
		return [
			'BlameableBehavior' => [
				'class' => BlameableBehavior::class,
			],
			'TimestampBehavior' => [
				'class' => TimestampBehavior::class,
				'value' => (new \DateTime)->format('Y-m-d H:i:s'),
			],
			'SoftDeleteBehavior' => [
				'class' => SoftDeleteBehavior::class,
				'softDeleteAttributeValues' => [
					'deleted' => static::YES,
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['membership_id', 'status'], 'required'],
			[['membership_id', 'payment_method', 'payment_processor', 'recurring_payment', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['created_at', 'updated_at'], 'safe'],
			[['created_at', 'updated_at'], 'default'],
			[['payer_id', 'card_id', 'payment_id', 'ip_address'], 'string', 'max' => 255],
			[['membership_id'], 'unique'],
			[['membership_id'], 'exist', 'skipOnError' => true, 'targetClass' => Membership::class, 'targetAttribute' => ['membership_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'membership_id' => Yii::t('label', 'Membership ID'),
			'payer_id' => Yii::t('label', 'Payer ID'),
			'card_id' => Yii::t('label', 'Card ID'),
			'payment_id' => Yii::t('label', 'Payment ID'),
			'payment_method' => Yii::t('label', 'Payment Method'),
			'payment_processor' => Yii::t('label', 'Payment Processor'),
			'recurring_payment' => Yii::t('label', 'Recurring Payment'),
			'ip_address' => Yii::t('label', 'IP Address'),
			'created_by' => Yii::t('label', 'Created By'),
			'updated_by' => Yii::t('label', 'Updated By'),
			'created_at' => Yii::t('label', 'Created At'),
			'updated_at' => Yii::t('label', 'Updated At'),
			'status' => Yii::t('label', 'Status'),
			'deleted' => Yii::t('label', 'Deleted'),
		];
	}

	/**
	 * Model payment method labels.
	 *
	 * @return array
	 */
	public static function getPaymentMethodLabels()
	{
		return [
			static::PAYMENT_METHOD_CASH => Yii::t('label', 'Cash'),
			static::PAYMENT_METHOD_BANK_TRANSFER => Yii::t('label', 'Bank Transfer'),
			static::PAYMENT_METHOD_CARD => Yii::t('label', 'Card'),
			static::PAYMENT_METHOD_ONLINE => Yii::t('label', 'Online'),
			static::PAYMENT_METHOD_BANCONTACT => Yii::t('label', 'Bancontact'),
		];
	}

	/**
	 * Model payment processor labels.
	 *
	 * @return array
	 */
	public static function getPaymentProcessorLabels()
	{
		return [
			static::PAYMENT_PROCESSOR_STRIPE => Yii::t('label', 'Stripe'),
			static::PAYMENT_PROCESSOR_PAYPAL => Yii::t('label', 'PayPal'),
			static::PAYMENT_PROCESSOR_BANK => Yii::t('label', 'Bank'),
		];
	}

	/**
	 * Model payment method labels.
	 *
	 * @return array
	 */
	public static function getPaymentTypeLabels()
	{
		return [
			self::PAYMENT_TYPE_MEMBERSHIP => Yii::t('label', 'Membership'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getMembership()
	{
		return $this->hasOne(Membership::class, ['id' => 'membership_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getCreator()
	{
		return $this->hasOne(User::class, ['id' => 'created_by']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getUpdater()
	{
		return $this->hasOne(User::class, ['id' => 'updated_by']);
	}
}

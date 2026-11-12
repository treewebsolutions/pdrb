<?php

namespace common\models;

use tws\behaviors\DateTimeBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%membership}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $member_id
 * @property string $code
 * @property string $start_at
 * @property string $end_at
 * @property string $price
 * @property string $currency
 * @property int $type
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property Membership $parent
 * @property Membership[] $memberships
 * @property Member $member
 * @property Currency $currencyModel
 * @property PaymentMetadata $paymentMetadata
 * @property User $creator
 * @property User $updater
 */
class Membership extends CommonActiveRecord
{
	const TYPE_DISTINGUISHED = 5;
	const TYPE_SUPPORTER = 6;
	const TYPE_SYMPATHIZER = 7;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_PENDING = 2;
	const STATUS_EXPIRED = 3;
	const STATUS_CANCELLED = 4;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%membership}}';
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
			'DateTimeBehavior' => [
				'class' => DateTimeBehavior::class,
				'attributes' => ['start_at', 'end_at'],
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
			[['member_id', 'code', 'status'], 'required'],
			[['parent_id', 'member_id', 'type', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
			[['start_at', 'end_at', 'created_at', 'updated_at'], 'default'],
			[['price'], 'number'],
			[['code'], 'string', 'max' => 255],
			[['currency'], 'string', 'max' => 3],
			[['code'], 'unique'],
			[['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Membership::class, 'targetAttribute' => ['parent_id' => 'id']],
			[['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::class, 'targetAttribute' => ['member_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('label', 'ID'),
			'parent_id' => Yii::t('label', 'Parent ID'),
			'member_id' => Yii::t('label', 'Member ID'),
			'code' => Yii::t('label', 'Code'),
			'start_at' => Yii::t('label', 'Start At'),
			'end_at' => Yii::t('label', 'End At'),
			'price' => Yii::t('label', 'Price'),
			'currency' => Yii::t('label', 'Currency'),
			'type' => Yii::t('label', 'Type'),
			'created_by' => Yii::t('label', 'Created By'),
			'updated_by' => Yii::t('label', 'Updated By'),
			'created_at' => Yii::t('label', 'Created At'),
			'updated_at' => Yii::t('label', 'Updated At'),
			'status' => Yii::t('label', 'Status'),
			'deleted' => Yii::t('label', 'Deleted'),
		];
	}

	/**
	 * Model type labels.
	 *
	 * @return array
	 */
	public static function getTypeLabels()
	{
		return [
			static::TYPE_DISTINGUISHED => Yii::t('member', 'Distinguished Member'),
			static::TYPE_SUPPORTER => Yii::t('member', 'Supporting Member'),
			static::TYPE_SYMPATHIZER => Yii::t('member', 'Sympathizing Member'),
		];
	}

	/**
	 * Human-readable membership period for receipts and payment descriptions.
	 *
	 * @return string|null
	 */
	public function getPeriodDescription()
	{
		if (!$this->start_at || !$this->end_at) {
			return null;
		}
		$formatter = Yii::$app->formatter;

		return $formatter->asDate($this->start_at, 'medium') . ' – ' . $formatter->asDate($this->end_at, 'medium');
	}

	/**
	 * Model status labels.
	 *
	 * @return array
	 */
	public static function getStatusLabels()
	{
		return [
			static::STATUS_INACTIVE => [
				'label' => Yii::t('label', 'Inactive'),
				'color' => 'secondary',
			],
			static::STATUS_ACTIVE => [
				'label' => Yii::t('label', 'Active'),
				'color' => 'success',
			],
			static::STATUS_PENDING => [
				'label' => Yii::t('label', 'Pending'),
				'color' => 'warning',
			],
			static::STATUS_EXPIRED => [
				'label' => Yii::t('label', 'Expired'),
				'color' => 'danger',
			],
			static::STATUS_CANCELLED => [
				'label' => Yii::t('label', 'Cancelled'),
				'color' => 'danger',
			],
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getParent()
	{
		return $this->hasOne(Membership::class, ['id' => 'parent_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getMemberships()
	{
		return $this->hasMany(Membership::class, ['parent_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getMember()
	{
		return $this->hasOne(Member::class, ['id' => 'member_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getCurrencyModel()
	{
		return $this->hasOne(Currency::class, ['iso_code' => 'currency']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getPaymentMetadata()
	{
		return $this->hasOne(PaymentMetadata::class, ['membership_id' => 'id']);
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

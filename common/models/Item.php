<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%item}}".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $item_id
 * @property int $type
 * @property int $quantity
 * @property string $price
 * @property string $currency
 * @property string $exchange_rate
 * @property int $multiplier
 * @property string $details
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property Invoice $invoice
 * @property Item $parentItem
 * @property Item[] $items
 * @property Currency $currencyModel
 * @property User $creator
 * @property User $updater
 *
 * @property string $totalPrice
 */
class Item extends CommonActiveRecord
{
	const TYPE_MEMBERSHIP = 1;
	const TYPE_PRODUCT = 2;
	const TYPE_SERVICE = 3;
	const TYPE_DONATION = 4;
	const TYPE_DISCOUNT = 5;
	const TYPE_TAX = 6;
	const TYPE_SHIPPING = 7;
	const TYPE_OTHER = 8;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%item}}';
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
			[['invoice_id', 'status'], 'required'],
			[['invoice_id', 'item_id', 'type', 'quantity', 'multiplier', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['price', 'exchange_rate'], 'number'],
			[['details'], 'string'],
			[['created_at', 'updated_at'], 'safe'],
			[['created_at', 'updated_at'], 'default'],
			[['currency'], 'string', 'max' => 3],
			[['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('label', 'ID'),
			'invoice_id' => Yii::t('label', 'Invoice ID'),
			'item_id' => Yii::t('label', 'Item ID'),
			'type' => Yii::t('label', 'Type'),
			'quantity' => Yii::t('label', 'Quantity'),
			'price' => Yii::t('label', 'Price'),
			'currency' => Yii::t('label', 'Currency'),
			'exchange_rate' => Yii::t('label', 'Exchange Rate'),
			'multiplier' => Yii::t('label', 'Multiplier'),
			'details' => Yii::t('label', 'Details'),
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
			static::TYPE_MEMBERSHIP => Yii::t('label', 'Membership'),
			static::TYPE_PRODUCT => Yii::t('label', 'Product'),
			static::TYPE_SERVICE => Yii::t('label', 'Service'),
			static::TYPE_DONATION => Yii::t('label', 'Donation'),
			static::TYPE_DISCOUNT => Yii::t('label', 'Discount'),
			static::TYPE_TAX => Yii::t('label', 'Tax'),
			static::TYPE_SHIPPING => Yii::t('label', 'Shipping'),
			static::TYPE_OTHER => Yii::t('label', 'Other'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getInvoice()
	{
		return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getParentItem()
	{
		return $this->hasOne(Item::class, ['id' => 'item_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getItems()
	{
		return $this->hasMany(Item::class, ['item_id' => 'id']);
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

	/**
	 * Gets the total price for the item (price multiplied by quantity).
	 *
	 * @return float
	 */
	public function getTotalPrice()
	{
		return (float) $this->price * (int) $this->quantity;
	}
}

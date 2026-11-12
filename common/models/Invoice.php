<?php

namespace common\models;

use tws\behaviors\DateTimeBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%invoice}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $member_id
 * @property string $external_id
 * @property string $stripe_payment_intent
 * @property string $document_series
 * @property int $document_number
 * @property string $issued_at
 * @property string $due_at
 * @property string $paid_at
 * @property int $payment_method
 * @property int $payment_processor
 * @property string $amount
 * @property string $currency
 * @property string $vat
 * @property string $exchange_rate
 * @property int $multiplier
 * @property string $e_invoice_upload_id
 * @property string $e_invoice_download_id
 * @property int $e_invoice_status
 * @property string $e_invoice_error
 * @property string $e_invoice_sent_at
 * @property string $details
 * @property int $type
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property Invoice $parent
 * @property Invoice[] $invoices
 * @property Member $member
 * @property Currency $currencyModel
 * @property Item[] $items
 * @property InvoiceHasTemplate[] $invoiceHasTemplates
 * @property Template[] $templates
 * @property User $creator
 * @property User $updater
 *
 * @property string $documentNumber
 * @property string $totalAmount
 */
class Invoice extends CommonActiveRecord
{
	const TYPE_INVOICE = 1;
	const TYPE_PROFORMA = 2;
	const TYPE_RECEIPT = 3;
	const TYPE_CREDIT_NOTE = 4;
	const TYPE_REFUND = 5;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DRAFT = 2;
	const STATUS_ISSUED = 3;
	const STATUS_PAID = 4;
	const STATUS_OVERDUE = 5;
	const STATUS_CANCELLED = 6;

	const PAYMENT_METHOD_CASH = 1;
	const PAYMENT_METHOD_BANK_TRANSFER = 2;
	const PAYMENT_METHOD_CARD = 3;
	const PAYMENT_METHOD_ONLINE = 4;
	const PAYMENT_METHOD_BANCONTACT = 5;

	const PAYMENT_PROCESSOR_STRIPE = 1;
	const PAYMENT_PROCESSOR_PAYPAL = 2;
	const PAYMENT_PROCESSOR_BANK = 3;

	const E_INVOICE_STATUS_NOT_SENT = 0;
	const E_INVOICE_STATUS_SENT = 1;
	const E_INVOICE_STATUS_PROCESSED = 2;
	const E_INVOICE_STATUS_ERROR = 3;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%invoice}}';
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
				'attributes' => ['issued_at', 'due_at', 'paid_at', 'e_invoice_sent_at'],
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
			[['status'], 'required'],
			[['parent_id', 'member_id', 'document_number', 'payment_method', 'payment_processor', 'multiplier', 'e_invoice_status', 'type', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['issued_at', 'due_at', 'paid_at', 'e_invoice_sent_at', 'created_at', 'updated_at'], 'safe'],
			[['issued_at', 'due_at', 'paid_at', 'e_invoice_sent_at', 'created_at', 'updated_at'], 'default'],
			[['amount', 'vat', 'exchange_rate'], 'number'],
			[['e_invoice_error', 'details'], 'string'],
			[['external_id', 'stripe_payment_intent', 'document_series', 'e_invoice_upload_id', 'e_invoice_download_id'], 'string', 'max' => 255],
			[['currency'], 'string', 'max' => 3],
			[['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['parent_id' => 'id']],
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
			'external_id' => Yii::t('label', 'External ID'),
			'stripe_payment_intent' => Yii::t('label', 'Stripe Payment Intent'),
			'document_series' => Yii::t('label', 'Document Series'),
			'document_number' => Yii::t('label', 'Document Number'),
			'issued_at' => Yii::t('label', 'Issued At'),
			'due_at' => Yii::t('label', 'Due At'),
			'paid_at' => Yii::t('label', 'Paid At'),
			'payment_method' => Yii::t('label', 'Payment Method'),
			'payment_processor' => Yii::t('label', 'Payment Processor'),
			'amount' => Yii::t('label', 'Amount'),
			'currency' => Yii::t('label', 'Currency'),
			'vat' => Yii::t('label', 'VAT'),
			'exchange_rate' => Yii::t('label', 'Exchange Rate'),
			'multiplier' => Yii::t('label', 'Multiplier'),
			'e_invoice_upload_id' => Yii::t('label', 'E-Invoice Upload ID'),
			'e_invoice_download_id' => Yii::t('label', 'E-Invoice Download ID'),
			'e_invoice_status' => Yii::t('label', 'E-Invoice Status'),
			'e_invoice_error' => Yii::t('label', 'E-Invoice Error'),
			'e_invoice_sent_at' => Yii::t('label', 'E-Invoice Sent At'),
			'details' => Yii::t('label', 'Details'),
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
			static::TYPE_INVOICE => Yii::t('label', 'Invoice'),
			static::TYPE_PROFORMA => Yii::t('label', 'Proforma'),
			static::TYPE_RECEIPT => Yii::t('label', 'Receipt'),
			static::TYPE_CREDIT_NOTE => Yii::t('label', 'Credit Note'),
			static::TYPE_REFUND => Yii::t('label', 'Refund'),
		];
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
			static::STATUS_DRAFT => [
				'label' => Yii::t('label', 'Draft'),
				'color' => 'secondary',
			],
			static::STATUS_ISSUED => [
				'label' => Yii::t('label', 'Issued'),
				'color' => 'info',
			],
			static::STATUS_PAID => [
				'label' => Yii::t('label', 'Paid'),
				'color' => 'success',
			],
			static::STATUS_OVERDUE => [
				'label' => Yii::t('label', 'Overdue'),
				'color' => 'warning',
			],
			static::STATUS_CANCELLED => [
				'label' => Yii::t('label', 'Cancelled'),
				'color' => 'danger',
			],
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
	 * Model e-invoice status labels.
	 *
	 * @return array
	 */
	public static function getEInvoiceStatusLabels()
	{
		return [
			static::E_INVOICE_STATUS_NOT_SENT => Yii::t('label', 'Not Sent'),
			static::E_INVOICE_STATUS_SENT => Yii::t('label', 'Sent'),
			static::E_INVOICE_STATUS_PROCESSED => Yii::t('label', 'Processed'),
			static::E_INVOICE_STATUS_ERROR => Yii::t('label', 'Error'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getParent()
	{
		return $this->hasOne(Invoice::class, ['id' => 'parent_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getInvoices()
	{
		return $this->hasMany(Invoice::class, ['parent_id' => 'id']);
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
	public function getItems()
	{
		return $this->hasMany(Item::class, ['invoice_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getInvoiceHasTemplates()
	{
		return $this->hasMany(InvoiceHasTemplate::class, ['invoice_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getTemplates()
	{
		return $this->hasMany(Template::class, ['id' => 'template_id'])->viaTable('{{%invoice_has_template}}', ['invoice_id' => 'id']);
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
	 * Gets the formatted document number including the series.
	 *
	 * @return string
	 */
	public function getDocumentNumber()
	{
		return implode('', array_filter([
			$this->document_series,
			$this->document_number,
		]));
	}

	/**
	 * Gets the total invoice amount including VAT.
	 *
	 * @return float
	 */
	public function getTotalAmount()
	{
		return (float) $this->amount + (float) $this->vat;
	}
}

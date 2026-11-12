<?php

namespace frontend\modules\payment\models;

use common\models\Invoice;
use common\models\Item;
use common\models\Membership;
use common\models\Template;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class PaymentResult extends Model
{
	/**
	 * @var string|int The Membership model ID.
	 */
	public $membership_id;

	/**
	 * @var string|int The payment processor payer ID.
	 */
	public $payer_id;

	/**
	 * @var string|int The payment processor card ID.
	 */
	public $card_id;

	/**
	 * @var string|int The payment processor payment ID.
	 */
	public $payment_id;

	/**
	 * @var string|int The payment processor transaction ID.
	 */
	public $transaction_id;

	/**
	 * @var Membership The Membership model.
	 */
	private $_membership;

	/**
	 * @var Invoice The Invoice model.
	 */
	private $_invoice;

	/**
	 * @var \DateTime The processing date and time instance.
	 */
	private $_processingDate;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->_processingDate = new \DateTime();
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['membership_id'], 'required'],
			[['membership_id'], 'exist', 'targetClass' => Membership::class, 'targetAttribute' => ['membership_id' => 'id'], 'skipOnError' => true],
			[['payer_id', 'card_id', 'payment_id', 'transaction_id'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'membership_id' => Yii::t('label', 'Membership ID'),
			'payer_id' => Yii::t('label', 'Payer ID'),
			'card_id' => Yii::t('label', 'Card ID'),
			'payment_id' => Yii::t('label', 'Payment ID'),
			'transaction_id' => Yii::t('label', 'Transaction ID'),
		]);
	}

	/**
	 * Gets the Membership model.
	 *
	 * @return Membership|null
	 */
	public function getMembership()
	{
		if (!$this->_membership) {
			$this->_membership = Membership::findOne([
				'id' => $this->membership_id,
				'deleted' => Membership::NO,
			]);
		}
		return $this->_membership;
	}

	/**
	 * Gets the Invoice model.
	 *
	 * @return Invoice|null
	 */
	public function getInvoice()
	{
		return $this->_invoice;
	}

	/**
	 * Activates the Membership model and sets the start/end dates.
	 *
	 * @return bool
	 */
	protected function saveMembership()
	{
		try {
			$membership = $this->getMembership();
			$startAt = $this->_processingDate->format('Y-m-d H:i:s');
			$endAt = (clone $this->_processingDate)->modify('+1 year')->format('Y-m-d H:i:s');

			$membership->detachBehavior('BlameableBehavior');
			$membership->start_at = $startAt;
			$membership->end_at = $endAt;
			$membership->status = Membership::STATUS_ACTIVE;
			if (!$membership->save()) {
				throw new \Exception('Membership cannot be activated.');
			}

			return true;
		} catch (\Exception $e) {
			$this->addError('', $e->getMessage());
			return false;
		}
	}

	/**
	 * Saves the PaymentMetadata model.
	 *
	 * @return bool
	 */
	protected function savePaymentMetadata()
	{
		try {
			$paymentMetadata = $this->getMembership()->paymentMetadata;
			if (!$paymentMetadata) {
				throw new \Exception('Payment metadata is missing.');
			}
			$paymentMetadata->detachBehavior('BlameableBehavior');
			$paymentMetadata->payer_id = (string) $this->payer_id;
			$paymentMetadata->payment_id = (string) $this->payment_id;
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
	 * Saves the Invoice and Item models.
	 *
	 * @return bool
	 */
	protected function saveInvoice()
	{
		try {
			$membership = $this->getMembership();
			$paymentMetadata = $membership->paymentMetadata;

			$invoice = new Invoice();
			$invoice->detachBehavior('BlameableBehavior');
			$invoice->member_id = $membership->member_id;
			$invoice->issued_at = $this->_processingDate->format('Y-m-d H:i:s');
			$invoice->paid_at = $this->_processingDate->format('Y-m-d H:i:s');
			$invoice->payment_method = $paymentMetadata ? $paymentMetadata->payment_method : null;
			$invoice->payment_processor = $paymentMetadata ? $paymentMetadata->payment_processor : null;
			$invoice->stripe_payment_intent = (string) $this->payment_id ?: null;
			$invoice->amount = $membership->price;
			$invoice->currency = $membership->currency ?: Yii::$app->formatter->currencyCode;
			$invoice->vat = Yii::$app->settings->get('vatRate', 'general') ?: 0;
			$invoice->setSerializedValue('details', [
				'transaction_id' => (string) $this->payment_id,
				'membership_code' => $membership->code,
			]);
			$invoice->type = Invoice::TYPE_INVOICE;
			$invoice->status = Invoice::STATUS_PAID;
			if (!$invoice->save()) {
				throw new \Exception('Invoice cannot be saved.');
			}
			$this->_invoice = $invoice;

			$item = new Item();
			$item->detachBehavior('BlameableBehavior');
			$item->invoice_id = $invoice->id;
			$item->item_id = $membership->id;
			$item->type = Item::TYPE_MEMBERSHIP;
			$item->quantity = 1;
			$item->price = $membership->price;
			$item->currency = $invoice->currency;
			$item->setSerializedValue('details', [
				'start_at' => $membership->start_at,
				'end_at' => $membership->end_at,
				'membership_code' => $membership->code,
			]);
			$item->status = Item::STATUS_ACTIVE;
			if (!$item->save()) {
				throw new \Exception('Invoice item cannot be saved.');
			}

			return true;
		} catch (\Exception $e) {
			$this->addError('', $e->getMessage());
			return false;
		}
	}

	/**
	 * Sends membership welcome email confirmation.
	 *
	 * @return bool
	 */
	protected function sendConfirmationEmail()
	{
		try {
			$membership = $this->getMembership();
			$member = $membership->member;
			if (!$member || empty($member->email)) {
				return false;
			}

			$template = Template::findDefaultByTypeAndVariant(Template::TYPE_EMAIL, Template::EMAIL_VARIANT_MEMBERSHIP_WELCOME);
			if (!$template || !($templateTranslation = $template->getTranslation())) {
				return false;
			}
			$shortCodeValues = [
				'{{APP_NAME}}' => Yii::$app->name,
				'{{FIRST_NAME}}' => $member->first_name,
				'{{LAST_NAME}}' => $member->last_name,
				'{{EMAIL}}' => $member->email,
				'{{PHONE}}' => $member->phone,
				'{{MEMBERSHIP_CODE}}' => $membership->code,
				'{{MEMBERSHIP_AMOUNT}}' => Yii::$app->formatter->asCurrency($membership->price, $membership->currency),
				'{{MEMBERSHIP_START_AT}}' => Yii::$app->formatter->asDate($membership->start_at),
				'{{MEMBERSHIP_END_AT}}' => Yii::$app->formatter->asDate($membership->end_at),
			];
			return Yii::$app->mailer->compose()
				->setTo([$member->email => $member->fullName])
				->setSubject(strtr($templateTranslation->subject, $shortCodeValues))
				->setHtmlBody(strtr($templateTranslation->content, $shortCodeValues))
				->send();
		} catch (\Exception $e) {
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
			if (!($membership = $this->getMembership())) {
				$this->addError('', Yii::t('frontend', 'Payment response is not valid.'));
				throw new \Exception();
			}

			if ($membership->status != Membership::STATUS_ACTIVE) {
				if (!$this->saveMembership()) {
					throw new \Exception();
				}
				if (!$this->savePaymentMetadata()) {
					throw new \Exception();
				}
				if (!$this->saveInvoice()) {
					throw new \Exception();
				}
				$this->sendConfirmationEmail();
			}

			$dbTransaction->commit();
			return true;
		} catch (\Exception $e) {
			$dbTransaction->rollBack();
			return false;
		}
	}
}

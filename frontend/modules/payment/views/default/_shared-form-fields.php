<?php
/* @var $this yii\web\View */
/* @var $form common\widgets\ActiveForm */
/* @var $model frontend\modules\payment\models\PaymentMembershipForm */

use common\models\PaymentMetadata;
use yii\helpers\Html;

$paymentSettings = Yii::$app->settings->getCategory('payment');
$activePaymentMethods = array_intersect_key(PaymentMetadata::getPaymentMethodLabels(), (array) $paymentSettings['paymentMethods']);
$activePaymentProcessors = array_intersect_key(PaymentMetadata::getPaymentProcessorLabels(), (array) $paymentSettings['paymentProcessors'][PaymentMetadata::PAYMENT_METHOD_CARD]);
?>

<?php if (count($activePaymentMethods) > 0): ?>
	<div class="form-group required gap-b-sm gap-t-sm">
		<label class="control-label"><?= Yii::t('common', 'Choose your payment method') ?></label>
		<div>
			<?= $form->field($model, 'payment_method', [
				'options' => [
					'class' => '',
				],
			])->radioList($activePaymentMethods, [
				'class' => 'radio-inline',
				'unselect' => null,
				'item' => function ($index, $label, $name, $checked, $value) use ($model) {
					$itemOptions = [
						'label' => $label,
						'value' => $value,
					];
					if ($value == PaymentMetadata::PAYMENT_METHOD_CARD) {
						$itemOptions['data']['requires-payment-processor'] = 'true';
					}
					return Html::tag('div', Html::radio($name, $checked, $itemOptions), ['class' => 'radio']);
				},
			])->label(false) ?>
			<div id="pm-hints" class="alert alert-icon alert-info">
				<?php if (array_key_exists(PaymentMetadata::PAYMENT_METHOD_CARD, $activePaymentMethods)): ?>
					<div id="pm-hint-<?= PaymentMetadata::PAYMENT_METHOD_CARD ?>" class="<?= $model->payment_method == PaymentMetadata::PAYMENT_METHOD_CARD ? '' : 'hidden' ?>"><?= Yii::t('frontend', 'This payment method requires a valid credit or debit card. The payment transaction is secured.') ?></div>
				<?php endif; ?>
				<?php if (array_key_exists(PaymentMetadata::PAYMENT_METHOD_BANK_TRANSFER, $activePaymentMethods)): ?>
					<div id="pm-hint-<?= PaymentMetadata::PAYMENT_METHOD_BANK_TRANSFER ?>" class="<?= $model->payment_method == PaymentMetadata::PAYMENT_METHOD_BANK_TRANSFER ? '' : 'hidden' ?>">
						<?= Yii::t('frontend', 'This payment method requires a manual bank transfer. Your membership will be manually activated after the payment is completed.') ?>
						<div class="table table-condensed table-borderless table-break-sm gap-t-sm">
							<div class="tbody">
								<div class="tr">
									<div class="th col-autowidth"><?= Yii::t('label', 'Bank Name') ?></div>
									<div class="td"><?= $paymentSettings['bankName'] ?></div>
								</div>
								<div class="tr">
									<div class="th col-autowidth"><?= Yii::t('label', 'Bank Account') ?></div>
									<div class="td"><?= $paymentSettings['bankAccount'] ?></div>
								</div>
								<div class="tr">
									<div class="th col-autowidth"><?= Yii::t('label', 'Bank BIC') ?></div>
									<div class="td"><?= $paymentSettings['bankBic'] ?></div>
								</div>
								<div class="tr">
									<div class="th col-autowidth"><?= Yii::t('label', 'Bank SWIFT') ?></div>
									<div class="td"><?= $paymentSettings['bankSwift'] ?></div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php else: ?>
	<?php
	$itemOptions = [];
	if ($model->payment_method == PaymentMetadata::PAYMENT_METHOD_CARD) {
		$itemOptions['data']['requires-payment-processor'] = 'true';
	}
	?>
	<?= $form->field($model, 'payment_method', [
		'options' => [
			'tag' => false,
		],
		'template' => '{input}',
	])->hiddenInput($itemOptions) ?>
<?php endif; ?>


<?php if (count($activePaymentProcessors) > 1): ?>
	<div class="gap-b-sm gap-t-sm">
		<?= $form->field($model, 'payment_processor', [
			'options' => [
				'id' => 'pp-container',
				'class' => 'gap-t-sm',
			],
		])->radioList($activePaymentProcessors, [
			'class' => 'radio-inline',
			'uncheck' => null,
			'item' => function ($index, $label, $name, $checked, $value) {
				$itemOptions = [
					'label' => $label,
					'value' => $value,
				];
				return Html::tag('div', Html::radio($name, $checked, $itemOptions), ['class' => 'radio']);
			},
		])->inline(true) ?>
	</div>
<?php else: ?>
	<?= $form->field($model, 'payment_processor', [
		'options' => [
			'tag' => false,
		],
		'template' => '{input}',
	])->hiddenInput() ?>
<?php endif; ?>

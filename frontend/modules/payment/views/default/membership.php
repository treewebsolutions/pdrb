<?php
/* @var $this yii\web\View */
/* @var $model frontend\modules\payment\models\PaymentMembershipForm */

use common\models\Member;
use common\models\PaymentMetadata;
use common\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('frontend', 'Payment for {item}', ['item' => Yii::t('label', 'Membership')]);
$this->params['breadcrumbs'][] = Html::encode($this->title);
\frontend\modules\payment\assets\PaymentAsset::register($this);

$paymentSettings = Yii::$app->settings->getCategory('payment');
$activePaymentMethods = array_intersect_key(PaymentMetadata::getPaymentMethodLabels(), (array) $paymentSettings['paymentMethods']);

$membership = $model->getMembership();
$member = $membership->member;
$memberTypeLabels = Member::getTypeLabels();
?>

<div class="section section-md">
	<div class="container-fluid">
		<header class="section-header">
			<p><?= Yii::t('frontend', 'Please review your membership and make a payment in order to activate it.') ?></p>
		</header>

		<?php $form = ActiveForm::begin([
			'id' => 'payment-form',
			'options' => [
				'novalidate' => true,
			],
			'validateOnType' => true,
		]); ?>
			<div class="form-group gap-b-md">
				<label class="control-label"><?= Yii::t('label', 'Member') ?></label>
				<div>
					<?= Html::encode($member ? $member->fullName : '') ?>
					<?php if ($member && isset($memberTypeLabels[$member->type])): ?>
						<span class="text-muted">— <?= Html::encode($memberTypeLabels[$member->type]) ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="form-group gap-b-md">
				<label class="control-label"><?= Yii::t('label', 'Membership Code') ?></label>
				<div><?= Html::encode($membership->code) ?></div>
			</div>

			<?php if ($membership->start_at || $membership->end_at): ?>
				<div class="form-group gap-b-md">
					<label class="control-label"><?= Yii::t('label', 'Period') ?></label>
					<div>
						<?= Yii::$app->formatter->asDate($membership->start_at) ?> &mdash;
						<?= Yii::$app->formatter->asDate($membership->end_at) ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="form-group gap-b-lg">
				<label class="control-label"><?= Yii::t('label', 'Amount') ?></label>
				<div class="font-md">
					<?= Yii::$app->formatter->asCurrency($membership->price, $membership->currency) ?>
				</div>
			</div>

			<?= $this->render('_shared-form-fields', [
				'form' => $form,
				'model' => $model,
			]) ?>

			<div class="form-group text-center gap-t-lg">
				<button type="submit" class="btn btn-lg btn-default btn-slide-right" id="btn-submit-payment">
					<span class="btn-submit-label-payment <?= $model->payment_method == PaymentMetadata::PAYMENT_METHOD_CARD ? '' : 'hidden' ?>">
						<?= Yii::t('common', 'Pay') ?>
						<span class="btn-submit-amount"><?= Yii::$app->formatter->asCurrency($membership->price, $membership->currency) ?></span>
					</span>
					<?php if (array_key_exists(PaymentMetadata::PAYMENT_METHOD_BANK_TRANSFER, $activePaymentMethods)): ?>
						<span class="btn-submit-label-submit <?= $model->payment_method != PaymentMetadata::PAYMENT_METHOD_CARD ? '' : 'hidden' ?>"><?= Yii::t('common', 'Submit') ?></span>
					<?php endif; ?>
				</button>
			</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>

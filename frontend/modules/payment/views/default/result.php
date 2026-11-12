<?php
/* @var $this yii\web\View */
/* @var $model \frontend\modules\payment\models\PaymentResult */
/* @var array|null $donationContext success (bool), amount (float|null) when donation checkout */

use common\models\Membership;
use tws\helpers\Url;
use yii\helpers\Html;

$this->title = Yii::t('frontend', 'Payment Result');
$this->params['breadcrumbs'][] = Html::encode($this->title);

$donationContext = $donationContext ?? null;
$membership = $model->getMembership();
$member = $membership ? $membership->member : null;
$membershipTypeLabels = Membership::getTypeLabels();
$errorLines = $model->hasErrors() ? $model->getErrorSummary(true) : [];
?>

<?php if ($donationContext && !empty($donationContext['success'])): ?>
<section class="error-section centred">
	<div class="auto-container">
		<div class="inner-box">
			<h2><?= Html::encode(Yii::t('frontend', 'Payment was successful.')) ?></h2>
			<p><?= Html::encode(Yii::t('frontend', 'Thank you for your donation.')) ?></p>
			<?php if (!empty($donationContext['amount'])): ?>
				<p>
					<strong><?= Html::encode(Yii::t('label', 'Amount')) ?>:</strong>
					<?= Html::encode(Yii::$app->formatter->asCurrency((float) $donationContext['amount'], 'EUR')) ?>
				</p>
			<?php endif; ?>
			<div class="btn-box clearfix">
				<a href="<?= Url::to(['/site/index']) ?>" class="theme-btn"><?= Html::encode(Yii::t('common', 'Home')) ?></a>
				<a href="<?= Url::to(['/site/contact']) ?>" class="theme-btn style-two"><?= Html::encode(Yii::t('frontend', 'Contact Us')) ?></a>
			</div>
		</div>
	</div>
</section>
<?php elseif ($donationContext && empty($donationContext['success'])): ?>
<!-- payment result — same layout as site/error (404) -->
<section class="error-section centred">
	<div class="auto-container">
		<div class="inner-box">
			<h2><?= Html::encode(Yii::t('frontend', 'Payment was unsuccessful.')) ?></h2>
			<p><?= Html::encode(Yii::t('frontend', 'Your payment could not be completed. No charge has been made.')) ?></p>
			<?php if (!empty($errorLines)): ?>
				<p><?= nl2br(Html::encode(implode("\n", $errorLines))) ?></p>
			<?php endif; ?>
			<p><?= Html::encode(Yii::t('frontend', 'What can you do now?')) ?></p>
			<div class="btn-box clearfix">
				<a href="<?= Url::to(['/site/donate']) ?>" class="theme-btn"><?= Html::encode(Yii::t('frontend', 'Try Again')) ?></a>
				<a href="<?= Url::to(['/site/index']) ?>" class="theme-btn style-two"><?= Html::encode(Yii::t('common', 'Home')) ?></a>
				<a href="<?= Url::to(['/site/contact']) ?>" class="theme-btn style-two"><?= Html::encode(Yii::t('frontend', 'Contact Us')) ?></a>
			</div>
		</div>
	</div>
</section>
<!-- payment result end -->
<?php elseif ($model->hasErrors()): ?>
<!-- payment result — same layout as site/error (404) -->
<section class="error-section centred">
	<div class="auto-container">
		<div class="inner-box">
			<h2><?= Html::encode(Yii::t('frontend', 'Payment was unsuccessful.')) ?></h2>
			<p><?= Html::encode(Yii::t('frontend', 'Your payment could not be completed. No charge has been made.')) ?></p>
			<?php if (!empty($errorLines)): ?>
				<p><?= nl2br(Html::encode(implode("\n", $errorLines))) ?></p>
			<?php endif; ?>
			<p><?= Html::encode(Yii::t('frontend', 'What can you do now?')) ?></p>
			<div class="btn-box clearfix">
				<?php if ($membership): ?>
					<a href="<?= Url::to(['/payment/default/membership', 'id' => Yii::$app->security->maskToken((string) $membership->id)]) ?>" class="theme-btn"><?= Html::encode(Yii::t('frontend', 'Try Again')) ?></a>
				<?php endif; ?>
				<a href="<?= Url::to(['/site/index']) ?>" class="theme-btn<?= $membership ? ' style-two' : '' ?>"><?= Html::encode(Yii::t('common', 'Home')) ?></a>
				<a href="<?= Url::to(['/site/contact']) ?>" class="theme-btn style-two"><?= Html::encode(Yii::t('frontend', 'Contact Us')) ?></a>
			</div>
		</div>
	</div>
</section>
<!-- payment result end -->
<?php else: ?>
<section class="error-section centred">
	<div class="auto-container">
		<div class="inner-box">
			<h2><?= Html::encode(Yii::t('frontend', 'Payment was successful.')) ?></h2>
			<p><?= Html::encode(Yii::t('frontend', 'Thank you for supporting our organization.')) ?></p>
			<?php if ($membership && $membership->status === Membership::STATUS_ACTIVE): ?>
				<p><?= Html::encode(Yii::t('frontend', 'Your membership is now active for the validity period below.')) ?></p>
			<?php endif; ?>
			<?php if ($membership): ?>
				<p>
					<strong><?= Html::encode(Yii::t('label', 'Member')) ?>:</strong>
					<?= Html::encode($member ? $member->fullName : '') ?>
					<?php if ($member && isset($membershipTypeLabels[$membership->type])): ?>
						(<?= Html::encode($membershipTypeLabels[$membership->type]) ?>)
					<?php endif; ?>
				</p>
				<p>
					<strong><?= Html::encode(Yii::t('label', 'Membership Code')) ?>:</strong>
					<?= Html::encode($membership->code) ?>
				</p>
				<?php if ($membership->start_at || $membership->end_at): ?>
					<p>
						<strong><?= Html::encode(Yii::t('label', 'Period')) ?>:</strong>
						<?= $membership->start_at ? Html::encode(Yii::$app->formatter->asDate($membership->start_at)) : '—' ?>
						&mdash;
						<?= $membership->end_at ? Html::encode(Yii::$app->formatter->asDate($membership->end_at)) : '—' ?>
					</p>
				<?php endif; ?>
				<p>
					<strong><?= Html::encode(Yii::t('label', 'Amount')) ?>:</strong>
					<?= Html::encode(Yii::$app->formatter->asCurrency($membership->price, $membership->currency ?: Yii::$app->formatter->currencyCode)) ?>
				</p>
			<?php endif; ?>
			<div class="btn-box clearfix">
				<a href="<?= Url::to(['/site/index']) ?>" class="theme-btn"><?= Html::encode(Yii::t('common', 'Home')) ?></a>
				<a href="<?= Url::to(['/site/contact']) ?>" class="theme-btn style-two"><?= Html::encode(Yii::t('frontend', 'Contact Us')) ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

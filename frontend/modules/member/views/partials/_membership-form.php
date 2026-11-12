<?php

use common\models\Member;
use common\widgets\ActiveForm;
use frontend\models\MembershipForm;
use yii\helpers\Html;

$memberModel = new MembershipForm();
$memberModel->role = Yii::t('frontend', 'Membership Application');
$memberModel->content = Yii::t('frontend', 'I am interested in becoming a member. Please provide me with more information about the membership process and requirements.');
?>

<?php $form = ActiveForm::begin([
	'action' => ['/site/membership'],
	'method' => 'post',
	'options' => ['class' => 'booking-form'],
]); ?>

	<div class="form-group">
		<?= $form->field($memberModel, 'last_name')->textInput([
			'placeholder' => Yii::t('label', 'Last Name'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'first_name')->textInput([
			'placeholder' => Yii::t('label', 'First Name'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'middle_name')->textInput([
			'placeholder' => Yii::t('label', 'Middle Name'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'email')->textInput([
			'type' => 'email',
			'placeholder' => Yii::t('label', 'Email'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'phone')->textInput([
			'type' => 'tel',
			'placeholder' => Yii::t('label', 'Phone'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'gender')->inline()->radioList(Member::getGenderLabels(), [
			'class' => 'gender-radio',
		])->label(Yii::t('label', 'Gender')) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'role')->textInput([
			'placeholder' => Yii::t('label', 'Subject'),
		])->label(false) ?>
	</div>

	<div class="form-group">
		<?= $form->field($memberModel, 'content')->textarea([
			'placeholder' => Yii::t('label', 'Message'),
			'rows' => 4,
		])->label(false) ?>
	</div>

	<?= $form->field($memberModel, 'workEmail')->hiddenInput()->label(false) ?>
	
	<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
		<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
		<?= $form->field($memberModel, 'captchaResponse', [
			'template' => '{input}',
		])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
	<?php endif; ?>

	<div class="form-group message-btn">
		<button type="submit"><?= Yii::t('frontend', 'Apply Now') ?></button>
	</div>

<?php ActiveForm::end(); ?>


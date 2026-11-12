<?php
/* @var $this yii\web\View */
/* @var $resetPasswordRequestModel common\models\ResetPasswordRequestForm */
/* @var $resetPasswordModel common\models\ResetPasswordForm */

use common\models\ResetPasswordForm;
use common\widgets\ActiveForm;
use tws\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = Html::encode($this->title);
?>

<!-- Page Title -->
<section class="page-title" style="background-image: url(<?= $this->context->currentPage->cover ? $this->context->currentPage->getCoverUrl(true) : Url::to('@web/img/banner.jpg') ?>);">
	<div class="auto-container">
		<div class="content-box">
			<div class="title centred">
				<h1><?= $this->title ?></h1>
			</div>
			<ul class="bread-crumb clearfix">
				<?php if ($breadcrumbs = $this->params['breadcrumbs']): ?>
					<?php
					if (!empty($breadcrumbs)) {
						$lastBreadcrumb = array_pop($breadcrumbs);
						$breadcrumbs[] = [
							'label' => $lastBreadcrumb,
							'template' => '<li class="active">{link}</li>',
						];
					}
					?>
					<?= Breadcrumbs::widget([
						'homeLink' => [
							'label' => '<i class="fa fa-home"></i>',
							'url' => implode('', array_filter([
								\tws\helpers\Url::base(true),
								(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
							])),
							'title' => \common\models\Page::findPageByRoute(['site/index'])->translation->title,
							'template' => '<li>{link}</li>',
						],
						'options' => [
							'class' => '',
						],
						'encodeLabels' => false,
						'links' => !empty($breadcrumbs) ? $breadcrumbs : [],
					]) ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</section>
<!-- End Page Title -->

<!-- contact-information -->
<section class="contact-information">
	<div class="auto-container">
		<?php if ($this->context->currentPage->content): ?>
			<div class="row clearfix">
				<div class="col-lg-12 col-md-12 col-sm-12 content-column">
					<div style="margin-bottom: 30px;">
						<?= $this->context->currentPage->content ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($resetPasswordModel->scenario == ResetPasswordForm::SCENARIO_PASSWORD): ?>
			<div class="row clearfix">
				<div class="col-lg-3 col-md-2 col-sm-12"></div>
				<div class="col-lg-6 col-md-8 col-sm-12 content-column">
					<div class="content_block_12">
						<div class="content-box contact-style-two">
							<h4 style="text-align: center; margin-bottom: 30px; padding-left: 0;"><?= Yii::t('common', 'Set your new password') ?></h4>
							<?php $form = ActiveForm::begin([
								'id' => mb_strtolower($resetPasswordModel->formName()),
								'options' => [
									'novalidate' => true,
									'class' => 'default-form',
								],
								'validateOnType' => true,
								'validateOnBlur' => false,
							]); ?>
							<div class="row clearfix">
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="form-group">
										<?= $form->field($resetPasswordModel, 'password', [
											'template' => '{input}{error}'
										])->passwordInput([
											'placeholder' => $resetPasswordModel->getAttributeLabel('password'),
											'required' => true
										])->label(false) ?>
									</div>
								</div>
								<?= $form->field($resetPasswordModel, 'workEmail', [
									'options' => [
										'class' => 'work-email',
									],
									'template' => '{input}',
								])->textarea(['required' => 'required'])->label(false) ?>
								<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
									<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
									<?= $form->field($resetPasswordModel, 'captchaResponse', [
										'template' => '{input}',
									])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
								<?php endif; ?>
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="message-btn centred">
										<?= Html::submitButton(Yii::t('common', 'Reset Password'), [
											'class' => 'theme-btn',
											'type' => 'submit',
										]) ?>
									</div>
								</div>
							</div>
							<?php ActiveForm::end(); ?>
							<div style="text-align: center; margin-top: 20px;">
								<p><?= Yii::t('common', 'Don\'t have an account yet?') ?> <?= Html::a(Yii::t('common', 'Sign Up'), ['/site/signup'], ['style' => 'color: #e41e2f;']) ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-2 col-sm-12"></div>
			</div>
		<?php else: ?>
			<div class="row clearfix">
				<div class="col-lg-6 col-md-6 col-sm-12 content-column">
					<div class="content_block_12">
						<div class="content-box contact-style-two">
							<h4 style="text-align: center; margin-bottom: 30px; padding-left: 0;"><?= Yii::t('common', 'Request a password reset code') ?></h4>
							<?php $form = ActiveForm::begin([
								'id' => mb_strtolower($resetPasswordRequestModel->formName()),
								'options' => [
									'novalidate' => true,
									'class' => 'default-form',
								],
								'validateOnType' => true,
								'validateOnBlur' => false,
							]); ?>
							<div class="row clearfix">
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="form-group">
										<?= $form->field($resetPasswordRequestModel, 'username', [
											'template' => '{input}{error}'
										])->textInput([
											'placeholder' => $resetPasswordRequestModel->getAttributeLabel('username'),
											'required' => true
										])->label(false) ?>
									</div>
								</div>
								<?= $form->field($resetPasswordRequestModel, 'workEmail', [
									'options' => [
										'class' => 'work-email',
									],
									'template' => '{input}',
								])->textarea(['required' => 'required'])->label(false) ?>
								<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
									<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
									<?= $form->field($resetPasswordRequestModel, 'captchaResponse', [
										'template' => '{input}',
									])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
								<?php endif; ?>
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="message-btn centred">
										<?= Html::submitButton(Yii::t('common', 'Request Code'), [
											'class' => 'theme-btn',
											'type' => 'submit',
										]) ?>
									</div>
								</div>
							</div>
							<?php ActiveForm::end(); ?>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-12 content-column">
					<div class="content_block_12">
						<div class="content-box contact-style-two">
							<h4 style="text-align: center; margin-bottom: 30px; padding-left: 0;"><?= Yii::t('common', 'Set your new password') ?></h4>
							<?php $form = ActiveForm::begin([
								'id' => mb_strtolower($resetPasswordModel->formName()),
								'options' => [
									'novalidate' => true,
									'class' => 'default-form',
								],
								'validateOnType' => true,
								'validateOnBlur' => false,
							]); ?>
							<div class="row clearfix">
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="form-group">
										<?= $form->field($resetPasswordModel, 'token', [
											'template' => '{input}{error}'
										])->textInput([
											'placeholder' => $resetPasswordModel->getAttributeLabel('token'),
											'required' => true
										])->label(false) ?>
									</div>
								</div>
								<?= $form->field($resetPasswordModel, 'workEmail', [
									'options' => [
										'class' => 'work-email',
									],
									'template' => '{input}',
								])->textarea(['required' => 'required'])->label(false) ?>
								<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
									<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
									<?= $form->field($resetPasswordModel, 'captchaResponse', [
										'template' => '{input}',
									])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
								<?php endif; ?>
								<div class="col-lg-12 col-md-12 col-sm-12 big-column">
									<div class="message-btn centred">
										<?= Html::submitButton(Yii::t('common', 'Validate Code'), [
											'class' => 'theme-btn',
											'type' => 'submit',
										]) ?>
									</div>
								</div>
							</div>
							<?php ActiveForm::end(); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<!-- contact-information end -->

<?php
$this->registerJs('
		$( "#' . Html::getInputId($resetPasswordModel, 'workEmail') . '").removeAttr("required");
	');
?>
<?php
$this->registerJs('
		$( "#' . Html::getInputId($resetPasswordRequestModel, 'workEmail') . '").removeAttr("required");
	');
?>



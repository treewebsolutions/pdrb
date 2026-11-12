<?php
/* @var $this yii\web\View */
/* @var $model common\models\SignupForm */

use common\widgets\ActiveForm;
use tws\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = Html::encode($this->title);
?>

<section class="blog">

	<div class="pagetitle wow fadeInUp">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1><?= $this->title ?></h1>
					<div class="breadcrumb">
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
									'template' => '<li>{link}</li><li>/</li>',
								],
								'options' => [
									'class' => '',
								],
								'encodeLabels' => false,
								'links' => !empty($breadcrumbs) ? $breadcrumbs : [],
							]) ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="blogpost">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<?= $this->context->currentPage->content ?>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="appoimentbg">
						<div class="appfrm">
							<?php $form = ActiveForm::begin([
								'id' => mb_strtolower($model->formName()),
								'options' => [
									'novalidate' => true,
								],
								'validateOnType' => true,
								'validateOnBlur' => false,
							]); ?>
							<div class="row">
								<div class="col-md-6">
									<?= $form->field($model, 'last_name')->textInput(['class' => 'form-control']) ?>
								</div>
								<div class="col-md-6">
									<?= $form->field($model, 'first_name')->textInput(['class' => 'form-control']) ?>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<?= $form->field($model, 'email')->input('email', ['class' => 'form-control']) ?>
								</div>
								<div class="col-md-4">
									<?= $form->field($model, 'phone')->input('tel', ['class' => 'form-control']) ?>
								</div>
								<div class="col-md-4">
									<?= $form->field($model, 'password')->passwordInput(['class' => 'form-control', 'autocomplete' => 'new-password']) ?>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<?= $form->field($model, 'acceptTerms')->checkbox([
											'uncheck' => null,
											'label' => Yii::t('common', 'I understand and agree to the {0} and {1} of {2}.', [
												Html::a(Yii::t('common', 'Terms and Conditions'), ['/site/terms-and-conditions'], ['target' => '_blank']),
												Html::a(Yii::t('common', 'Privacy Policy'), ['/site/privacy-policy'], ['target' => '_blank']),
												Yii::$app->name
											]),
										]) ?>
									</div>
								</div>
								<?= $form->field($model, 'workEmail', [
									'options' => [
										'class' => 'work-email',
									],
									'template' => '{input}',
								])->textarea(['required' => 'required'])->label(false) ?>
								<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
									<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
									<?= $form->field($model, 'captchaResponse', [
										'template' => '{input}',
									])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
								<?php endif; ?>
								<div class="col-md-12">
									<span class="pull-right gap-t-xs gap-b-xs"><span class="color-danger">*</span> <?= Yii::t('common', 'Required Fields') ?></span>
								</div>
								<div class="col-md-12 text-center">
									<?= Html::submitButton(Yii::t('common', 'Sign Up'), [
										'class' => 'btn-default',
									]) ?>
								</div>
							</div>
							<?php ActiveForm::end(); ?>
							<div class="text-center" style="margin-top: 10px;">
								<p><?= Yii::t('common', 'Already have an account?') ?> <?= Html::a(Yii::t('common', 'Log In'), ['/site/login'], ['class' => 'link']) ?></p>
								<p><?= Html::a(Yii::t('common', 'Forgot Password?'), ['/site/reset-password'], ['class' => 'link']) ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</section>

<?php
$this->registerJs('
		$( "#' . Html::getInputId($model, 'workEmail') . '").removeAttr("required");
	');
?>

<?php
/* @var $this yii\web\View */
/* @var $model frontend\models\DonationForm */

use frontend\models\DonationForm;
use kartik\touchspin\TouchSpin;
use tws\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$amountMin = $model->getAmountMin();

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

<!-- donation-information -->
<section class="contact-information">
	<div class="auto-container">
		<div class="row clearfix">
			<div class="col-lg-12 col-md-12 col-sm-12 content-column">
				<div class="content_block_12">
					<div class="content-box contact-style-two">
						<?= $this->context->currentPage->content ?>
						<?php $form = ActiveForm::begin([
							'id' => mb_strtolower($model->formName()),
							'action' => ['/site/donate'],
							'options' => [
								'novalidate' => true,
								'class' => 'default-form donation-form',
							],
							'validateOnType' => true,
							'validateOnBlur' => false,
						]); ?>
						<div class="row clearfix">
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'last_name', ['template' => '{input}{error}'])->textInput([
									'placeholder' => $model->getAttributeLabel('last_name'),
									'required' => true,
								])->label(false) ?>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'first_name', ['template' => '{input}{error}'])->textInput([
									'placeholder' => $model->getAttributeLabel('first_name'),
									'required' => true,
								])->label(false) ?>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'email', ['template' => '{input}{error}'])->input('email', [
									'placeholder' => $model->getAttributeLabel('email'),
									'required' => true,
								])->label(false) ?>
							</div>
						</div>
						<div class="row clearfix">
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'phone', ['template' => '{input}{error}'])->textInput([
									'placeholder' => $model->getAttributeLabel('phone'),
									'required' => true,
								])->label(false) ?>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'amount', ['template' => '{input}{error}'])->widget(TouchSpin::class, [
									'options' => [
										'placeholder' => Yii::t('common', 'Example') . ': ' . $amountMin,
									],
									'pluginOptions' => [
										'initval' => (int) ($model->amount !== null && $model->amount !== '' ? $model->amount : $amountMin),
										'min' => $amountMin,
										'max' => DonationForm::AMOUNT_MAX,
										'step' => DonationForm::AMOUNT_STEP,
										'decimals' => 0,
										'boostat' => 5,
										'maxboostedstep' => 10,
										'verticalbuttons' => true,
										'prefix' => 'EUR',
										'postfix' => '',
										'buttondown_class' => 'btn btn-default donation-touchspin-btn',
										'buttonup_class' => 'btn btn-default donation-touchspin-btn',
									],
								])->label(false) ?>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-12 big-column">
								<?= $form->field($model, 'checkoutPaymentMethod', [
									'template' => '{input}{error}',
								])->dropDownList([
									DonationForm::CHECKOUT_PAYMENT_CARD => Yii::t('frontend', 'Visa / Mastercard'),
									DonationForm::CHECKOUT_PAYMENT_BANCONTACT => Yii::t('frontend', 'Bancontact'),
								], [
									'class' => 'ignore',
									'prompt' => Yii::t('frontend', 'Payment method'),
									'required' => true,
								])->label(false) ?>
							</div>
						</div>
						<div class="row clearfix">
							<?= $form->field($model, 'workEmail', [
								'options' => ['class' => 'work-email'],
								'template' => '{input}',
							])->textarea(['required' => 'required'])->label(false) ?>
							<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
								<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
								<?= $form->field($model, 'captchaResponse', ['template' => '{input}'])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
							<?php endif; ?>
							<div class="col-lg-12 col-md-12 col-sm-12 big-column">
								<div class="message-btn">
									<?= Html::submitButton(Yii::t('frontend', 'Donate'), [
										'class' => 'theme-btn',
										'type' => 'submit',
										'name' => 'submit-donation',
									]) ?>
								</div>
							</div>
						</div>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- donation-information end -->

<?php
$this->registerCss('
		/* Ensure select elements with ignore class are visible and styled */
		select.ignore {
			display: block !important;
			visibility: visible !important;
			opacity: 1 !important;
			width: 100%;
			height: 58px;
			padding: 8px 12px;
			font-size: 14px;
			line-height: 1.42857143;
			color: #555;
			background-color: #fff;
			background-image: none;
			border: 1px solid #e8e8e8;
			border-radius: 5px;
			-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
			-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
		}
		select.ignore:focus {
			border-color: #999;
			outline: 0;
			-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(153,153,153,.6);
			box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(153,153,153,.6);
		}
		select.ignore:hover {
			border-color: #dbdbdb;
		}
		/* Hide placeholder option from select dropdown list */
		select.ignore option[value=""] {
			display: none;
		}
		/* Donation amount: EUR prefix, gri +/- cu bordură */
		.donation-form .bootstrap-touchspin .input-group-addon.bootstrap-touchspin-prefix {
			background: #fff;
			border: 1px solid #e8e8e8;
			color: #555;
			font-weight: 600;
		}
		.donation-form .bootstrap-touchspin .form-control {
			border-color: #e8e8e8;
		}
		.donation-form .bootstrap-touchspin .input-group-btn-vertical > .btn.donation-touchspin-btn {
			background: #f0f0f0 !important;
			background-image: none !important;
			border: 1px solid #c8c8c8 !important;
			color: #555 !important;
			box-shadow: none !important;
			text-shadow: none !important;
			margin-left: 0 !important;
		}
		.donation-form .bootstrap-touchspin .input-group-btn-vertical > .btn.donation-touchspin-btn:hover,
		.donation-form .bootstrap-touchspin .input-group-btn-vertical > .btn.donation-touchspin-btn:focus {
			background: #e3e3e3 !important;
			border-color: #b0b0b0 !important;
			color: #333 !important;
		}
		.donation-form .bootstrap-touchspin .input-group-btn-vertical .bootstrap-touchspin-up {
			border-radius: 0 !important;
			border-top-right-radius: 4px !important;
		}
		.donation-form .bootstrap-touchspin .input-group-btn-vertical .bootstrap-touchspin-down {
			margin-top: -1px !important;
			border-radius: 0 !important;
			border-bottom-right-radius: 4px !important;
		}
	');

$this->registerJs('
	$( "#' . Html::getInputId($model, 'workEmail') . '").removeAttr("required");

	$(document).ready(function() {
		$("select.ignore").each(function() {
			$(this).css({
				"display": "block",
				"visibility": "visible",
				"opacity": "1"
			});
		});

		$("select.ignore").on("focus click", function() {
			var $select = $(this);
			var placeholderOption = $select.find("option[value=\'\']");
			if (placeholderOption.length) {
				placeholderOption.prop("disabled", true);
				placeholderOption.css("display", "none");
			}
		});

		$("select.ignore").on("mousedown", function() {
			var $select = $(this);
			var placeholderOption = $select.find("option[value=\'\']");
			if (placeholderOption.length) {
				placeholderOption.prop("disabled", true);
				placeholderOption.css("display", "none");
			}
		});
	});
');

$this->registerJs('
		var maxHeight = 0;
		$(".equal").each(function(){
			if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
		});
		$(".equal").height(maxHeight);
	');
?>

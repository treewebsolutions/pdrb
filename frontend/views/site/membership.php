<?php
/* @var $this yii\web\View */
/* @var $model frontend\models\MembershipForm */
/* @var $contact array */
/* @var $address string */
/* @var $type string|null */
/* @var $typeMessage array */
/* @var $typeLabels array */
/* @var $typeTitle string|null */

use common\models\Country;
use common\models\Member;
use frontend\models\MembershipForm;
use tws\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Breadcrumbs;

// Set title if typeTitle is provided
if (!empty($combinedTitle)) {
	$this->title = $combinedTitle;
}

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

<!-- membership-information -->
<section class="contact-information">
	<div class="auto-container">
		<?php
		// Format address for display (add line breaks)
		$displayAddress = !empty($contact['streetNumber']) 
			? str_replace($contact['streetNumber'], str_replace(', ', ',<br><span style="margin-left: 5px;"></span>', $contact['streetNumber']), $address)
			: $address;
		?>
		
		<div class="row clearfix">
			<div class="col-lg-8 col-md-12 col-sm-12 content-column">
				<div class="content_block_12">
					<div class="content-box contact-style-two">
					<?php $form = ActiveForm::begin([
						'id' => mb_strtolower($model->formName()),
						'action' => ['/site/membership'],
						'options' => [
							'novalidate' => true,
							'class' => 'default-form',
						],
						'validateOnType' => true,
						'validateOnBlur' => false,
					]); ?>
					<div class="row clearfix">
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'last_name', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('last_name'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'first_name', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('first_name'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'middle_name', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('middle_name')
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'email', [
								'template' => '{input}{error}'
							])->input('email', [
								'placeholder' => $model->getAttributeLabel('email'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'phone', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('phone'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'gender', [
								'template' => '{label}{input}{error}'
							])->inline()->radioList(Member::getGenderLabels(), [
								'class' => 'gender-radio-membership',
							]) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'address', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('address'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'locality', [
								'template' => '{input}{error}'
							])->textInput([
								'placeholder' => $model->getAttributeLabel('locality'),
								'required' => true
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'country', [
								'template' => '{input}{error}'
							])->dropDownList(ArrayHelper::map(Country::findAllCountries(), 'iso_alpha2', 'translation.name'), [
								'required' => true,
								'class' => 'ignore',
								'prompt' => Yii::t('label', 'Country')
							])->label(false) ?>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<?= $form->field($model, 'type', [
								'template' => '{input}{error}'
							])->dropDownList($typeLabels, [
								'required' => true,
								'class' => 'ignore',
								'prompt' => Yii::t('label', 'Type')
							])->label(false) ?>
						</div>
						<div id="membership-payment-method-wrap" class="col-lg-4 col-md-4 col-sm-12 big-column"<?= $model->getRequiresPayment() ? '' : ' style="display: none;"' ?>>
							<?= $form->field($model, 'checkoutPaymentMethod', [
								'template' => '{input}{error}',
							])->dropDownList([
								MembershipForm::CHECKOUT_PAYMENT_CARD => Yii::t('frontend', 'Visa / Mastercard'),
								MembershipForm::CHECKOUT_PAYMENT_BANCONTACT => Yii::t('frontend', 'Bancontact'),
							], [
								'class' => 'ignore',
								'prompt' => Yii::t('frontend', 'Payment method'),
							])->label(false) ?>
						</div>
					<?php if (!isset($typeLabels[\common\models\Member::TYPE_VOLUNTEER]) || count($typeLabels) > 1): ?>
					<div class="col-lg-12 col-md-12 col-sm-12 big-column" style="margin-bottom: 20px;">
						<small>
							<?= Yii::t('frontend', 'MEMBERSHIP_NOTES') ?>
						</small>
					</div>
					<?php endif; ?>
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
						<div class="col-lg-12 col-md-12 col-sm-12 big-column">
							<div class="message-btn">
								<?= Html::submitButton(Yii::t('frontend', 'Apply Now'), [
									'class' => 'theme-btn',
									'type' => 'submit',
									'name' => 'submit-form',
								]) ?>
							</div>
						</div>
					</div>
						<?php ActiveForm::end(); ?>
					</div>
				</div>
			</div>
			
			<div class="col-lg-4 col-md-12 col-sm-12 content-column">
				<div class="content_block_13">
					<div class="content-box">
						<?php if ($contact['mobilePhone'] || $contact['fixedPhone']): ?>
							<?php
							$phones = [];
							if ($contact['mobilePhone']) {
								$phones = array_filter(array_merge($phones, explode(',', $contact['mobilePhone'])));
							}
							if ($contact['fixedPhone']) {
								$phones = array_filter(array_merge($phones, explode(',', $contact['fixedPhone'])));
							}
							?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-emergency-call"></i></div>
								<ul class="info clearfix">
									<?php foreach ($phones as $index => $phone): ?>
										<li>
											<h5><?= $index == 0 ? Yii::t('label', 'Phone') : Yii::t('label', 'Alternative Phone') ?></h5>
											<p><?= Html::a($phone, "tel:{$phone}", ['data-prevent-page-overlay' => 'true']) ?></p>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						
						<?php if ($contact['email']): ?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-mail-inbox-app"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Yii::t('label', 'Email') ?></h5>
										<p><a href="mailto:<?= $contact['email'] ?>" data-prevent-page-overlay="true"><?= $contact['email'] ?></a></p>
									</li>
								</ul>
							</div>
						<?php endif; ?>
						
						<?php if ($address): ?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-map-1"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Yii::t('label', 'Address') ?></h5>
										<p>
											<?php if ($contact['latitude'] && $contact['longitude']): ?>
												<a href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $displayAddress ?></a>
											<?php else: ?>
												<?= $displayAddress ?>
											<?php endif; ?>
										</p>
									</li>
								</ul>
							</div>
						<?php endif; ?>
						
						<?php if ($contact['schedule'][Yii::$app->language]): ?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-clock"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Yii::t('label', 'Working Hours') ?></h5>
										<p><?= nl2br($contact['schedule'][Yii::$app->language]) ?></p>
									</li>
								</ul>
							</div>
						<?php endif; ?>
						
						<?php $socialNetwork = Yii::$app->settings->getCategory('socialNetwork'); ?>
						<?php if ($socialNetwork['facebookPage'] || $socialNetwork['twitterPage'] || $socialNetwork['instagramPage'] || $socialNetwork['linkedinPage'] || $socialNetwork['youtubeChannel'] || $socialNetwork['pinterestPage'] || $socialNetwork['tiktokPage']): ?>
						<div class="single-item">
							<div class="icon-box"><i class="flaticon-share"></i></div>
							<ul class="info clearfix">
								<li>
									<h5><?= Yii::t('label', 'Social Media') ?></h5>
									<p>
										<?php if ($socialNetwork['facebookPage']): ?>
											<a href="<?= $socialNetwork['facebookPage'] ?>" title="Facebook" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-facebook"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['instagramPage']): ?>
											<a href="<?= $socialNetwork['instagramPage'] ?>" title="Instagram" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-instagram"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['twitterPage']): ?>
											<a href="<?= $socialNetwork['twitterPage'] ?>" title="Twitter" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-twitter"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['linkedinPage']): ?>
											<a href="<?= $socialNetwork['linkedinPage'] ?>" title="LinkedIn" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-linkedin"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['pinterestPage']): ?>
											<a href="<?= $socialNetwork['pinterestPage'] ?>" title="Pinterest" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-pinterest"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['youtubeChannel']): ?>
											<a href="<?= $socialNetwork['youtubeChannel'] ?>" title="Youtube" target="_blank" style="display: inline-block; margin-right: 10px;">
												<i class="fab fa-youtube"></i>
											</a>
										<?php endif; ?>
										<?php if ($socialNetwork['tiktokPage']): ?>
											<a href="<?= $socialNetwork['tiktokPage'] ?>" title="TikTok" target="_blank" style="display: inline-block; margin-right: 10px;">
												<svg xmlns="http://www.w3.org/2000/svg" height="0.9em" viewBox="0 0 448 512" style="fill: currentColor;"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg>
											</a>
										<?php endif; ?>
									</p>
								</li>
							</ul>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- membership-information end -->

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
	');
	
    $this->registerJs('
		$( "#' . Html::getInputId($model, 'workEmail') . '").removeAttr("required");
		
		// Ensure select elements with ignore class are visible
		$(document).ready(function() {
			$("select.ignore").each(function() {
				$(this).css({
					"display": "block",
					"visibility": "visible",
					"opacity": "1"
				});
			});
			
			// Hide placeholder option from dropdown list when select is opened
			$("select.ignore").on("focus click", function() {
				var $select = $(this);
				var placeholderOption = $select.find("option[value=\'\']");
				if (placeholderOption.length) {
					// Temporarily disable placeholder option
					placeholderOption.prop("disabled", true);
					placeholderOption.css("display", "none");
				}
			});
			
			// Also hide placeholder on mousedown (before dropdown opens)
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
(function () {
	var paidTypeIds = ' . Json::encode(array_values(array_map('intval', array_keys(MembershipForm::getAnnualFees())))) . ';
	var cardValue = ' . Json::encode(MembershipForm::CHECKOUT_PAYMENT_CARD) . ';
	var $type = $("#' . Html::getInputId($model, 'type') . '");
	var $wrap = $("#membership-payment-method-wrap");
	var $payment = $("#' . Html::getInputId($model, 'checkoutPaymentMethod') . '");
	function syncMembershipPaymentOptions() {
		var id = parseInt($type.val(), 10);
		var needsPayment = paidTypeIds.indexOf(id) >= 0;
		if (needsPayment) {
			$wrap.show();
			$payment.prop("disabled", false);
			if (!$payment.val()) {
				$payment.val(cardValue);
			}
		} else {
			$wrap.hide();
			$payment.prop("disabled", true).val("");
		}
	}
	$type.on("change", syncMembershipPaymentOptions);
	$(syncMembershipPaymentOptions);
})();
	');
?>

<?php
$this->registerJs('
		var maxHeight = 0;
		$(".equal").each(function(){
			if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
		});
		$(".equal").height(maxHeight);
	');
?>


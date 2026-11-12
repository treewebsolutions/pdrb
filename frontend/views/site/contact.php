<?php
/* @var $this yii\web\View */
/* @var $model frontend\models\ContactForm */
/* @var $contact array */
/* @var $address string */
/* @var $map dosamigos\google\maps\Map */

use tws\helpers\Url;
use yii\bootstrap\ActiveForm;
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
		<?php
		$contact = Yii::$app->settings->getCategory('contact');
		$address = implode(', ', array_filter([
			$contact['streetName'],
			str_replace(', ', ',<br><span style="margin-left: 5px;"></span>', $contact['streetNumber']),
			$contact['locality'],
			$contact['zipCode'],
			$contact['county'],
			$contact['country'] ? \common\models\Country::findAllCountries()[$contact['country']]->translation->name : null,
		]));
		?>
		
		<div class="row clearfix">
			<div class="col-lg-8 col-md-12 col-sm-12 content-column">
				<div class="content_block_12">
					<div class="content-box contact-style-two">
						<?php $form = ActiveForm::begin([
							'id' => mb_strtolower($model->formName()),
							'options' => [
								'novalidate' => true,
								'class' => 'default-form',
							],
							'validateOnType' => true,
							'validateOnBlur' => false,
						]); ?>
					<div class="row clearfix">
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<div class="form-group">
								<?= $form->field($model, 'name', [
									'template' => '{input}{error}'
								])->textInput([
									'placeholder' => $model->getAttributeLabel('name'),
									'required' => true
								])->label(false) ?>
							</div>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<div class="form-group">
								<?= $form->field($model, 'email', [
									'template' => '{input}{error}'
								])->input('email', [
									'placeholder' => $model->getAttributeLabel('email'),
									'required' => true
								])->label(false) ?>
							</div>
						</div>
						<div class="col-lg-4 col-md-4 col-sm-12 big-column">
							<div class="form-group">
								<?= $form->field($model, 'phone', [
									'template' => '{input}{error}'
								])->textInput([
									'placeholder' => $model->getAttributeLabel('phone')
								])->label(false) ?>
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 big-column">
							<div class="form-group">
								<?= $form->field($model, 'subject', [
									'template' => '{input}{error}'
								])->textInput([
									'placeholder' => $model->getAttributeLabel('subject'),
									'required' => true
								])->label(false) ?>
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 big-column">
							<div class="form-group">
								<?= $form->field($model, 'message', [
									'template' => '{input}{error}'
								])->textarea([
									'rows' => 6,
									'placeholder' => $model->getAttributeLabel('message')
								])->label(false) ?>
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
						<div class="col-lg-12 col-md-12 col-sm-12 big-column">
							<div class="message-btn">
								<?= Html::submitButton(Yii::t('common', 'Send'), [
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
												<a href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $address ?></a>
											<?php else: ?>
												<?= $address ?>
											<?php endif; ?>
										</p>
									</li>
								</ul>
							</div>
						<?php endif; ?>

						<?php if (\common\models\Branch::findAllBranches() && ($branchPage = \common\models\Page::findPageByRoute(['site/branch']))): ?>
							<div class="single-item">
								<div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Html::encode($branchPage->translation->title ?: Yii::t('common', 'Branches')) ?></h5>
										<p><?= Html::a(Yii::t('frontend', 'See all branches'), ['/site/branch']) ?></p>
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
						
						<?php if ($contact['fax']): ?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-fax"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Yii::t('label', 'Fax') ?></h5>
										<p><?= Html::a($contact['fax'], "tel:{$contact['fax']}", ['data-prevent-page-overlay' => 'true']) ?></p>
									</li>
								</ul>
							</div>
						<?php endif; ?>
						
						<?php if ($contact['company']): ?>
							<div class="single-item">
								<div class="icon-box"><i class="flaticon-apartment"></i></div>
								<ul class="info clearfix">
									<li>
										<h5><?= Yii::t('label', 'Company Information') ?></h5>
										<p>
											<?= implode ('<br>', array_filter([
												$contact['company'],
												($contact['tin'] ? Yii::t('label', 'Taxpayer Identification Number') . ': ' . $contact['tin'] : ''),
												($contact['registrationNumber'] ? Yii::t('label', 'Registration Number') . ': ' . $contact['registrationNumber'] : ''),
											])) ?>
										</p>
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
<!-- contact-information end -->

<?php if ($map): ?>
<!-- google-map-section -->
<section class="google-map-section">
	<div class="map-inner">
		<div class="google-map">
			<?= $map->display() ?>
		</div>
	</div>
</section>
<!-- google-map-section end -->
<?php endif; ?>

<?php
    $this->registerJs('
		$( "#' . Html::getInputId($model, 'workEmail') . '").removeAttr("required");
	');
?>

<?php if ($map): ?>
<?php
$this->registerJs('
    (function(){
        var mapInitialized = false;
        var mapInitAttempts = 0;
        var isInitializing = false; // Prevent multiple simultaneous calls
        function hasRenderedMap() {
            return $(".google-map iframe, .google-map > div").length > 0;
        }
        function isApiFullyReady() {
            // Comprehensive check that API is fully loaded and constructors work
            try {
                if (typeof google === "undefined" || !google.maps) {
                    return false;
                }
                // Test that LatLng can actually be instantiated
                var testLatLng = new google.maps.LatLng(0, 0);
                if (!testLatLng || typeof testLatLng.lat !== "function") {
                    return false;
                }
                // Check other required constructors
                return (typeof google.maps.LatLng === "function" &&
                        typeof google.maps.Map === "function" &&
                        typeof google.maps.Marker === "function");
            } catch(e) {
                return false;
            }
        }
        function initializeMap() {
            // Prevent multiple simultaneous initialization attempts
            if (isInitializing) {
                return;
            }
            if (mapInitialized && hasRenderedMap()) { 
                return; 
            }
            var apiReady = isApiFullyReady();
            var containerReady = $(".google-map").length > 0;
            if (apiReady && containerReady) {
                isInitializing = true; // Set flag to prevent concurrent calls
                $(".map-inner, .google-map").css({ width: "100%", height: "500px", display: "block" });
                if (typeof initialize === "function") {
                    // Add small delay to ensure API is fully ready
                    setTimeout(function() {
                        try { 
                            initialize(); 
                            // Post-check after initialize() to ensure tiles injected
                            setTimeout(function(){
                                if (hasRenderedMap()) {
                                    $(".google-map iframe, .google-map > div").css({ width: "100%", height: "100%" });
                                    mapInitialized = true;
                                } else {
                                    mapInitialized = false;
                                }
                                isInitializing = false; // Reset flag
                            }, 200);
                        } catch(e) { 
                            console.log("Map initialization error:", e);
                            mapInitialized = false;
                            isInitializing = false; // Reset flag on error
                            // Retry if error occurs
                            if (mapInitAttempts < 100) {
                                mapInitAttempts++;
                                setTimeout(initializeMap, 300);
                            }
                        }
                    }, 100); // Small delay to ensure API is fully ready
                } else {
                    isInitializing = false;
                }
                return;
            }
            if (mapInitAttempts < 200) { // allow more retries across slow loads
                mapInitAttempts++;
                setTimeout(initializeMap, 200);
            }
        }

        // Expose for async Google callbacks (common names)
        window.initializeMap = initializeMap;
        window.initMap = initializeMap;

        $(document).ready(initializeMap);
        $(window).on("load pageshow", initializeMap);
        document.addEventListener("visibilitychange", function(){ if (!document.hidden) { initializeMap(); } });
        $(document).on("shown.bs.tab shown.bs.collapse", initializeMap);
        $(document).on("pjax:end ajaxComplete", initializeMap);

        // Fallback triggers in case async load completes after onload
        setTimeout(initializeMap, 500);
        setTimeout(initializeMap, 1500);
    })();
');
?>
<?php endif; ?>

<?php
$this->registerJs('
		var maxHeight = 0;
		$(".equal").each(function(){
			if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
		});
		$(".equal").height(maxHeight);
	');
?>

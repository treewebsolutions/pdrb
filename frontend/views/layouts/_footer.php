<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;
use tws\helpers\Url;
use yii\widgets\ActiveForm;

?>

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

<!-- main-footer -->
<footer class="main-footer">
	<div class="widget-section">
		<div class="auto-container">
			<div class="row clearfix">
				<div class="col-lg-3 col-md-6 col-sm-12 footer-column">
					<div class="footer-widget contact-widget">
						<div class="widget-title">
							<h3><a href="<?= Url::to(['/site/index']) ?>" title="<?= Yii::$app->name ?>"><img src="<?= Url::to('@uploads/' . Yii::$app->settings->get('appLogoAlt')) ?: Url::to('@web/img/logo-alt.png') ?>" alt="<?= Yii::$app->name ?>" style="max-height: 50px; margin-right: 10px;"><?= Yii::$app->name ?></a></h3>
						</div>
						<div class="widget-content">
							<?php if ($appDescription = Yii::$app->settings->get('appDescription', 'general')): ?>
								<ul class="info clearfix">
									<li>
										<p>
											<?= $appDescription[Yii::$app->language] ?>
										</p>
									</li>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-6 col-sm-12 footer-column">
					<div class="footer-widget contact-widget">
						<div class="widget-title">
							<h3><?= Yii::t('frontend', 'Contact') ?></h3>
						</div>
						<div class="widget-content">
							<ul class="info clearfix">
								<?php if ($contact['company']): ?>
									<li>
										<p>
											<i class="fas fa-building"></i>
											<?= implode ('<br>', array_filter([
												$contact['company'],
												($contact['tin'] ? Yii::t('label', 'Taxpayer Identification Number') . ': ' . $contact['tin'] : ''),
												($contact['registrationNumber'] ? Yii::t('label', 'Registration Number') . ': ' . $contact['registrationNumber'] : ''),
											])) ?>
										</p>
									</li>
								<?php endif; ?>
								<?php if ($address): ?>
									<li>
										<p>
											<i class="fas fa-map-pin"></i>
					                        <?php if ($contact['latitude'] && $contact['longitude']): ?>
						                        <a href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $address ?></a>
					                        <?php else: ?>
						                        <?= $address ?>
					                        <?php endif; ?>
										</p>
									</li>
								<?php endif; ?>
								<?php if ($contact['schedule'][Yii::$app->language]): ?>
									<li>
										<p>
											<i class="fas fa-clock-o"></i>
											<?= nl2br($contact['schedule'][Yii::$app->language]) ?>
										</p>
									</li>
								<?php endif; ?>
								<?php if ($contact['mobilePhone'] || $contact['fixedPhone']): ?>
									<li>
										<?php
											$phones = [];
											if ($contact['mobilePhone']) {
												$phones = array_filter(array_merge($phones, explode(',', $contact['mobilePhone'])));
											}
											if ($contact['fixedPhone']) {
												$phones = array_filter(array_merge($phones, explode(',', $contact['fixedPhone'])));
											}
										?>
										<?php
											$numbers = [];
											foreach ($phones as $phone) {
												$numbers[] = Html::a($phone, "tel:{$phone}", ['data-prevent-page-overlay' => 'true']);
											}
										?>
										<p>
											<i class="fas fa-phone"></i>
											<?= Html::a($phones[0], "tel:{$phones[0]}", ['data-prevent-page-overlay' => 'true']) ?>
										</p>
									</li>
								<?php endif; ?>
								<?php if ($contact['fax']): ?>
									<li>
										<p>
											<i class="fas fa-fax"></i>
											<?= Html::a($contact['fax'], "tel:{$contact['fax']}", ['data-prevent-page-overlay' => 'true']) ?>
										</p>
									</li>
								<?php endif; ?>
								<?php if ($contact['email']): ?>
									<li>
										<p>
											<i class="fas fa-envelope"></i>
											<a href="mailto:<?= $contact['email'] ?>" data-prevent-page-overlay="true"><?= $contact['email'] ?></a>
										</p>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-6 col-sm-12 footer-column">
					<div class="footer-widget contact-widget">
						<div class="widget-title">
							<h3><?= Yii::t('frontend', 'Social Networks') ?></h3>
						</div>
						<div class="widget-content">
							<ul class="social-links clearfix" style="display:inline-flex; color: #fff;">
								<?php $socialNetwork = Yii::$app->settings->getCategory('socialNetwork'); ?>
								<?php if ($socialNetwork['facebookPage']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['facebookPage'] ?>" title="Facebook" target="_blank">
											<i class="fab fa-facebook"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['instagramPage']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['instagramPage'] ?>" title="Instagram" target="_blank">
											<i class="fab fa-instagram"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['twitterPage']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['twitterPage'] ?>" title="Twitter" target="_blank">
											<i class="fab fa-twitter"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['linkedinPage']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['linkedinPage'] ?>" title="Linked In" target="_blank">
											<i class="fab fa-linkedin"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['pinterestPage']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['pinterestPage'] ?>" title="Pinterest" target="_blank">
											<i class="fab fa-pinterest"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['youtubeChannel']): ?>
									<li style="margin-right: 14px;">
										<a href="<?= $socialNetwork['youtubeChannel'] ?>" title="Youtube" target="_blank">
											<i class="fab fa-youtube"></i>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($socialNetwork['tiktokPage']): ?>
									<li>
										<a class="tiktok" href="<?= $socialNetwork['tiktokPage'] ?>" title="TikTok" target="_blank">
											<svg xmlns="http://www.w3.org/2000/svg" height="0.9em" viewBox="0 0 448 512" class="footer-tiktok"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg.footer-tiktok{fill:#8e91a2}</style><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg>
										</a>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-6 col-sm-12 footer-column">
					<div class="footer-widget links-widget">
						<div class="widget-title">
							<h3><?= Yii::t('frontend', 'Links') ?></h3>
						</div>
						<div class="widget-content">
							<?php if ($navMenu = \common\models\Menu::findDefaultFooterMenu()): ?>
								<?= \common\widgets\sitemenu\SiteMenu::widget([
									'options' => [
										'class' => 'links-list clearfix',
									],
									'activateParents' => true,
									'encodeLabels' => false,
									'items' => $navMenu->getNestedItems(),
								]) ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="footer-bottom">
		<div class="auto-container">
			<div class="bottom-inner clearfix">
				<div class="copyright text-center">
					<p><?= Yii::t('common', '&copy; {0} {1}. All rights reserved.', [Yii::$app->name, date('Y')]) ?> <a href="https://treewebsolutions.com" target="_blank" title="Tree Web Solutions"><?= Yii::t('common', 'Developed by {0}.', 'TWS') ?></a>.</p>
				</div>
			</div>
		</div>
	</div>
</footer>
<!-- main-footer end -->



<!--Scroll to top-->
<button class="scroll-top scroll-to-target" data-target="html">
	<span class="fas fa-angle-up"></span>
</button>
</div>

<?php if (!Yii::$app->getRequest()->getCookies()->has('acceptCookies')): ?>
    <div id="cookiebar">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-8" style="padding: 0 15px;">
                <h3 style="margin: 0;"><?= Yii::t('frontend', '{0} uses COOKIES', [Yii::$app->name]) ?></h3>
                <p><?= Yii::t('frontend', 'By clicking on "Yes, I accept" you agree to the use of cookies installed on the site. These are used to give you the best browsing experience possible.') ?></p>
            </div>
            <div class="col-md-3">
                <?php $form = ActiveForm::begin([
                    'action' =>['/accept-cookies'],
                    'id' => 'accept-cookies-form',
                ]); ?>
                <?= Html::hiddenInput('acceptCookies', 1); ?>
                <?= Html::hiddenInput('backUrl', Url::canonical()); ?>
                <?= Html::submitButton('&nbsp;&nbsp;&nbsp;' . Yii::t('frontend', 'Yes, I Accept'), ['id' => 'okcookies']) ?>
                <?= Html::a(Yii::t('common', 'Details'), ['/site/cookie-policy']); ?>
                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
<?php endif; ?>

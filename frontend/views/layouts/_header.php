<?php
/* @var $this \yii\web\View */

use common\models\User;
use yii\helpers\Html;
use tws\helpers\Url;

/** @var \common\models\User $user */
$user = Yii::$app->user->identity;
$appLanguages = \common\models\Language::findAllLanguages();
$appLanguagesCount = count($appLanguages);
$currentAppLanguage = $appLanguages[Yii::$app->language];

$membershipPage = \common\models\Page::findPageByRoute(['/site/membership']);
$donationPage = \common\models\Page::findPageByRoute(['/site/donate']);
?>

<?php
    $contact = Yii::$app->settings->getCategory('contact');
	$address = implode(', ', array_filter([
		$contact['streetName'],
		str_replace(', ', ',<br><span style="margin-left: 5px;"></span>', $contact['streetNumber']),
		$contact['locality'],
		$contact['country'] ? \common\models\Country::findAllCountries()[$contact['country']]->translation->name : null,
	]));
    if ($contact['mobilePhone'] || $contact['fixedPhone']) {
        $phones = [];
        if ($contact['mobilePhone']) {
            $phones = array_filter(array_merge($phones, explode(',', $contact['mobilePhone'])));
        }
        if ($contact['fixedPhone']) {
            $phones = array_filter(array_merge($phones, explode(',', $contact['fixedPhone'])));
        }
    }
?>

<div class="boxed_wrapper">

	<!-- search-popup -->
	<div id="search-popup" class="search-popup">
		<div class="close-search"><i class="fal fa-times"></i></div>
		<div class="popup-inner">
			<div class="overlay-layer"></div>
			<div class="search-form">
				<?php
				// Get the search page to use its slug
				$searchPage = \common\models\Page::findPageByRoute(['/search/default/index']);
				$searchUrl = $searchPage ? ['/' . $searchPage->translation->slug] : ['/search/default/index'];
				?>
				<?php $searchForm = \yii\widgets\ActiveForm::begin([
					'action' => $searchUrl,
					'method' => 'get',
				]); ?>
					<div class="form-group">
						<div class="input-group">
							<input type="search" class="form-control" name="q" value="<?= Yii::$app->request->get('q') ?>" placeholder="<?= Yii::t('frontend', 'Search') ?>..." required>
							<div class="input-group-append">
								<button type="submit" class="theme-btn style-four"><i class="far fa-search"></i></button>
							</div>
						</div>
					</div>
				<?php \yii\widgets\ActiveForm::end(); ?>
			</div>
		</div>
	</div>
	<!-- search-popup end -->

	<!-- sidebar cart item -->
	<div class="xs-sidebar-group info-group info-sidebar">
		<div class="xs-overlay xs-bg-black"></div>
		<div class="xs-sidebar-widget">
			<div class="sidebar-widget-container">
				<div class="widget-heading">
					<a href="#" class="close-side-widget"><i class="fal fa-times"></i></a>
				</div>
				<div class="sidebar-textwidget">
					<div class="sidebar-info-contents">
						<div class="content-inner">
							<div class="logo">
								<a href="<?= Url::to(['/site/index']) ?>"><img src="<?= Url::to('@uploads/' . Yii::$app->settings->get('appLogoAlt')) ?: Url::to('@web/img/logo-alt.png') ?>" alt="<?= Yii::$app->name ?>" style="max-height: 50px;"></a>
							</div>
							<div class="contact-info">
								<h4><?= Yii::t('frontend', 'Contact Info') ?></h4>
								<ul>
									<?php if ($address): ?>
										<li>
											<i class="fas fa-map-pin"></i>
											<?php if ($contact['latitude'] && $contact['longitude']): ?>
												<a href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $address ?></a>
											<?php else: ?>
												<?= $address ?>
											<?php endif; ?>
										</li>
									<?php endif; ?>
									<?php if (isset($phones) && !empty($phones)): ?>
										<li><i class="fas fa-phone"></i> <a href="tel:<?= $phones[0] ?>"><?= $phones[0] ?></a></li>
									<?php endif; ?>
									<?php if ($contact['email']): ?>
										<li><i class="fas fa-envelope"></i> <a href="mailto:<?= $contact['email'] ?>"><?= $contact['email'] ?></a></li>
									<?php endif; ?>
								</ul>
							</div>
							<ul class="social-box">
								<?php $socialNetwork = Yii::$app->settings->getCategory('socialNetwork'); ?>
								<?php if ($socialNetwork['facebookPage']): ?>
									<li class="facebook"><a href="<?= $socialNetwork['facebookPage'] ?>" title="Facebook" target="_blank"><i class="fab fa-facebook"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['instagramPage']): ?>
									<li class="instagram"><a href="<?= $socialNetwork['instagramPage'] ?>" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['twitterPage']): ?>
									<li class="twitter"><a href="<?= $socialNetwork['twitterPage'] ?>" title="Twitter" target="_blank"><i class="fab fa-twitter"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['linkedinPage']): ?>
									<li class="linkedin"><a href="<?= $socialNetwork['linkedinPage'] ?>" title="LinkedIn" target="_blank"><i class="fab fa-linkedin"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['pinterestPage']): ?>
									<li class="pinterest"><a href="<?= $socialNetwork['pinterestPage'] ?>" title="Pinterest" target="_blank"><i class="fab fa-pinterest"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['youtubeChannel']): ?>
									<li class="youtube"><a href="<?= $socialNetwork['youtubeChannel'] ?>" title="YouTube" target="_blank"><i class="fab fa-youtube"></i></a></li>
								<?php endif; ?>
								<?php if ($socialNetwork['tiktokPage']): ?>
									<li class="tiktok"><a href="<?= $socialNetwork['tiktokPage'] ?>" title="TikTok" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" height="0.9em" viewBox="0 0 448 512" style="fill: currentColor;"><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg></a></li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END sidebar widget item -->

	<!-- main header -->
	<header class="main-header style-one">
		<!-- header-top -->
		<div class="header-top">
			<div class="auto-container">
				<div class="top-inner clearfix">
					<div class="left-column pull-left clearfix">
						<ul class="info-list clearfix">
							<?php if ($membershipPage): ?>
								<li>
									<i class="fas fa-user-plus"></i>
									<a href="<?= Url::to(['/site/membership'], true) ?>" title="<?= Yii::t('frontend', 'Become Member') ?>"><?= Yii::t('frontend', 'Become Member') ?></a>
								</li>
							<?php endif; ?>
							<?php if ($donationPage): ?>
								<li>
									<i class="fas fa-donate"></i>
									<a href="<?= Url::to(['/site/donate'], true) ?>" title="<?= Yii::t('frontend', 'Donate') ?>"><?= Yii::t('frontend', 'Donate') ?></a>
								</li>
							<?php endif; ?>
							<?php if (!$membershipPage && !$donationPage): ?>
								<?php if (!$contact['mobilePhone'] || $contact['fixedPhone']): ?>
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
										<i class="flaticon-phone-with-wire"></i>
										<?= Html::a($phones[0], "tel:{$phones[0]}", ['data-prevent-page-overlay' => 'true']) ?>
									</li>
								<?php endif; ?>
								<?php if ($contact['email']): ?>
									<li>
										<i class="flaticon-mail-inbox-app"></i>
										<a href="mailto:<?= $contact['email'] ?>" data-prevent-page-overlay="true"><?= $contact['email'] ?></a>
									</li>
								<?php endif; ?>
							<?php endif; ?>
						</ul>
					</div>
					<div class="right-column pull-right clearfix">
						<ul class="social-links clearfix">
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
										<svg xmlns="http://www.w3.org/2000/svg" height="0.9em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- header-lower -->
		<div class="header-lower">
			<div class="auto-container">
				<div class="outer-box clearfix">
					<div class="logo-box pull-left">
						<figure class="logo"><a href="<?= Url::to(['/site/index']) ?>" title="<?= Yii::$app->name ?>"><img src="<?= Url::to('@uploads/' . Yii::$app->settings->get('appLogo')) ?: Url::to('@web/img/logo.png') ?>" alt="<?= Yii::$app->name ?>" style="max-height: 50px;"></a></figure>
					</div>
					<div class="menu-area clearfix pull-right">
						<!--Mobile Navigation Toggler-->
						<div class="mobile-nav-toggler">
							<i class="icon-bar"></i>
							<i class="icon-bar"></i>
							<i class="icon-bar"></i>
						</div>
						<nav class="main-menu navbar-expand-md navbar-light">
							<div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
								<?php if ($topBarMenu = \common\models\Menu::findDefaultTopBarMenu()): ?>
									<?php
									$items = $topBarMenu->getNestedItems(null, \common\models\MenuItem::STATUS_ACTIVE);
									?>
									<?php if (!empty($items)): ?>
										<?= \common\widgets\sitemenu\SiteMenuBs5::widget([
											'options' => [
												'class' => 'navigation clearfix',
											],
											'activateParents' => true,
											'encodeLabels' => false,
											'items' => $items,
										]) ?>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</nav>
						<div class="menu-right-content clearfix">
							<?php if ($appLanguagesCount > 1): ?>
								<div class="language-box">
									<span class="text">
										<img src="<?= Url::to("@web/img/flags/{$currentAppLanguage->country}.png") ?>" alt="<?= $currentAppLanguage->name ?>" style="margin-right: 5px;">
										<?= mb_strtoupper(mb_substr($currentAppLanguage->language_id, 0, 2), 'UTF-8') ?>
									</span>
									<ul class="language-list clearfix">
										<?php foreach ($appLanguages as $language): ?>
											<?php if ($language->language_id != Yii::$app->language): ?>
												<li><img src="<?= Url::to("@web/img/flags/{$language->country}.png") ?>" alt="<?= $language->name ?>" style="margin-right: 5px;"><a href="<?= Url::to(['/site/index', 'language' => $language->language_id]) ?>"><?=  mb_strtoupper(mb_substr($language->language_id, 0, 2), 'UTF-8') ?></a></li>
											<?php endif; ?>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							<ul class="other-option clearfix">
								<li class="search-btn">
									<button type="button" class="search-toggler" title="<?= Yii::t('frontend', 'Search in blog') ?>"><i class="far fa-search"></i></button>
								</li>
								<?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->status == User::STATUS_ACTIVE && Yii::$app->user->identity->getHasPermissions()): ?>
									<li>
										<a href="<?= Url::to('/admin') ?>" title="<?= Yii::t('common', 'Admin Panel') ?>" target="_blank"><i class="fas fa-tachometer-alt" aria-hidden="true"></i></a>
									</li>
									<li>
										<a href="<?= Url::to(['/site/logout']) ?>" title="<?= Yii::t('common', 'Log Out') ?>" data-confirm="<?= Yii::t('common', 'Are you sure you want to perform this operation?') ?>"><i class="fas fa-sign-out" aria-hidden="true"></i></a>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!--sticky Header-->
		<div class="sticky-header">
			<div class="auto-container">
				<div class="outer-box clearfix">
					<div class="logo-box pull-left">
						<figure class="logo"><a href="<?= Url::to(['/site/index']) ?>" title="<?= Yii::$app->name ?>"><img src="<?= Url::to('@uploads/' . Yii::$app->settings->get('appLogo')) ?: Url::to('@web/img/logo.png') ?>" alt="<?= Yii::$app->name ?>" style="max-height: 50px;"></a></figure>
					</div>
				<div class="menu-area clearfix pull-right">
					<nav class="main-menu clearfix"></nav>
					<div class="menu-right-content clearfix">
						<?php if ($appLanguagesCount > 1): ?>
							<div class="language-box">
								<span class="text">
									<img src="<?= Url::to("@web/img/flags/{$currentAppLanguage->country}.png") ?>" alt="<?= $currentAppLanguage->name ?>" style="margin-right: 5px;">
									<?= mb_strtoupper(mb_substr($currentAppLanguage->language_id, 0, 2), 'UTF-8') ?>
								</span>
								<ul class="language-list clearfix">
									<?php foreach ($appLanguages as $language): ?>
										<?php if ($language->language_id != Yii::$app->language): ?>
											<li><img src="<?= Url::to("@web/img/flags/{$language->country}.png") ?>" alt="<?= $language->name ?>" style="margin-right: 5px;"><a href="<?= Url::to(['/site/index', 'language' => $language->language_id]) ?>"><?=  mb_strtoupper(mb_substr($language->language_id, 0, 2), 'UTF-8') ?></a></li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						<ul class="other-option clearfix">
							<li class="search-btn">
								<button type="button" class="search-toggler" title="<?= Yii::t('frontend', 'Search in blog') ?>"><i class="far fa-search"></i></button>
							</li>
							<?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->status == User::STATUS_ACTIVE && Yii::$app->user->identity->getHasPermissions()): ?>
								<li>
									<a href="<?= Url::to('/admin') ?>" title="<?= Yii::t('common', 'Admin Panel') ?>" target="_blank"><i class="fas fa-tachometer-alt" aria-hidden="true"></i></a>
								</li>
								<li>
									<a href="<?= Url::to(['/site/logout']) ?>" title="<?= Yii::t('common', 'Log Out') ?>" data-confirm="<?= Yii::t('common', 'Are you sure you want to perform this operation?') ?>"><i class="fas fa-sign-out" aria-hidden="true"></i></a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
				</div>
			</div>
		</div>
	</header>
	<!-- main-header end -->

	<!-- Mobile Menu  -->
	<div class="mobile-menu">
		<div class="menu-backdrop"></div>
		<div class="close-btn"><i class="fas fa-times"></i></div>
		
		<nav class="menu-box">
			<div class="nav-logo">
				<a href="<?= Url::to(['/site/index']) ?>" title="<?= Yii::$app->name ?>"><img src="<?= Url::to('@uploads/' . Yii::$app->settings->get('appLogoAlt')) ?: Url::to('@web/img/logo.png') ?>" alt="<?= Yii::$app->name ?>" style="max-height: 50px;"></a>
			</div>
			<div class="menu-outer"></div>
			<div class="contact-info">
				<h4><?= Yii::t('frontend', 'Contact Info') ?></h4>
				<ul>
					<?php if ($address): ?>
						<li>
							<i class="fas fa-map-marker-alt"></i>
							<?php if ($contact['latitude'] && $contact['longitude']): ?>
								<a href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $address ?></a>
							<?php else: ?>
								<?= $address ?>
							<?php endif; ?>
						</li>
					<?php endif; ?>
					<?php if (isset($phones) && !empty($phones)): ?>
						<li><i class="fas fa-phone"></i> <a href="tel:<?= $phones[0] ?>"><?= $phones[0] ?></a></li>
					<?php endif; ?>
					<?php if ($contact['email']): ?>
						<li><i class="fas fa-envelope"></i> <a href="mailto:<?= $contact['email'] ?>"><?= $contact['email'] ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="social-links">
				<ul class="clearfix">
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
								<svg xmlns="http://www.w3.org/2000/svg" height="0.9em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</nav>
	</div><!-- End Mobile Menu -->
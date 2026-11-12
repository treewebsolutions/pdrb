<?php

use common\helpers\StringHelper;
use common\models\Article;
use common\models\Member;
use common\models\Page;
use common\models\Service;
use common\widgets\ActiveForm;
use tws\helpers\Url;
use yii\helpers\Html;

if ($homepageCarousel = \common\models\Carousel::findDefaultCarousel(\common\models\Carousel::TYPE_MAIN)) {
	$homepageCarouselItems = $homepageCarousel->getOrderedCarouselItems();
}

$latestArticles = Article::findLatestArticles(8, [Article::TYPE_BLOG]);
$featuredArticles = Article::findFeaturedArticles(8, [Article::TYPE_BLOG]);
$members = Member::findAllMembers(null, Member::TYPE_FOUNDER);

?>

<?php if ($homepageCarousel && $homepageCarouselItems): ?>
<section class="banner-section style-one">
	<div class="banner-carousel owl-theme owl-carousel owl-dots-none">
		<?php foreach ($homepageCarouselItems as $carouselItem): ?>
			<div class="slide-item">
				<div class="image-layer" style="background-image:url(<?= $carouselItem->imageUrl ?>)"></div>
				<div class="auto-container">
					<div class="content-box">
						<?php if ($carouselItem->translation->title): ?>
							<h1><?= $carouselItem->translation->title ?></h1>
						<?php endif; ?>
						<?php if ($carouselItem->translation->content): ?>
							<p><?= $carouselItem->translation->content ?></p>
						<?php endif; ?>
						<?php if (!empty($carouselItem->translation->anchor) && !empty($carouselItem->translation->url)): ?>
							<div class="btn-box">
								<?= Html::a($carouselItem->translation->anchor, $carouselItem->translation->url, [
									'class' => 'theme-btn',
									'target' => $carouselItem->target ?: null,
								]) ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="about-section sec-pad bg-color-1">
	<div class="auto-container">
		<div class="row clearfix">
			<div class="col-lg-12">
				<?= $this->context->currentPage->content ?>
			</div>
		</div>
	</div>
</section>


<?php if ($featuredArticles || $latestArticles): ?>
<!-- schedules-section -->
<section class="schedules-section sec-pad bg-color-1">
	<div class="bg-layer" style="background-image: url(<?= Url::to('@web/images/background/schedule-bg.jpg') ?>);"></div>
	<div class="auto-container">
		<div class="title-inner">
			<div class="row clearfix">
				<div class="col-lg-6 col-md-6 col-sm-12 title-column">
					<div class="sec-title light">
						<h2><?= Yii::t('frontend', 'Articles') ?></h2>
						<div class="title-shape"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="schedule-tab">
			<div class="tab-btn-box">
				<ul class="tab-btns schedule-tab-btns clearfix">
					<li class="p-tab-btn active-btn" data-tab="#tab-1"><?= Yii::t('frontend', 'Newest') ?></li>
					<li class="p-tab-btn" data-tab="#tab-2"><?= Yii::t('frontend', 'Featured') ?></li>
				</ul>
			</div>
			<div class="inner-content">
				<div class="row clearfix">
					<div class="col-lg-8 col-md-12 col-sm-12 inner-column">
						<div class="p-tabs-content">
							<div class="p-tab active-tab" id="tab-1">
								<div class="two-column-carousel owl-carousel owl-theme owl-dots-none">
									<?php foreach ($latestArticles as $latestArticle): ?>
										<?php $slug = str_replace([Article::TYPE_BLOG, Article::TYPE_ACTIVITY], ['article', 'activity'], $latestArticle->type)  ?>
										<div class="schedule-block-one">
											<div class="inner-box">
												<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>">
													<div class="image-box">
														<figure class="image img-ratio">
																<?php if ($latestArticle->imageUrl): ?>
																	<img class="img-responsive img-ratio-object" src="<?= $latestArticle->imageUrl ?>" alt="<?= $latestArticle->translation->title ?>">
																<?php else: ?>
																	<img class="img-responsive img-ratio-object" src="<?= Url::to('@web/img/announcement.jpg') ?>" alt="<?= $latestArticle->translation->title ?>">
																<?php endif; ?>
														</figure>
														<div class="content-box">
															<div class="post-date"><h3><?= date('d', strtotime($latestArticle->created_at)) ?><span><?= date('M', strtotime($latestArticle->created_at)) ?>’<?= date('y', strtotime($latestArticle->created_at)) ?></span></h3></div>
														</div>
													</div>
												</a>
												<div class="lower-content">
													<div class="equal">
														<div class="text mb-4">
															<h4><a class="article-title" href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>"><?= $latestArticle->translation->title ?></a></h4>
														</div>
														<p class="mb-4"><?= StringHelper::truncate(strip_tags($latestArticle->translation->content), 300) ?></p>
													</div>
													<div class="links">
														<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>" class="read-btn">
															<?= Yii::t('frontend', 'Read More') ?> <i class="flaticon-right-arrow"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="p-tab" id="tab-2">
								<div class="two-column-carousel owl-carousel owl-theme owl-dots-none">
									<?php foreach ($featuredArticles as $featuredArticle): ?>
										<?php $slug = str_replace([Article::TYPE_BLOG, Article::TYPE_ACTIVITY], ['article', 'activity'], $featuredArticle->type)  ?>
										<div class="schedule-block-one">
											<div class="inner-box">
												<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>">
													<div class="image-box">
														<figure class="image img-ratio">
															<?php if ($featuredArticle->imageUrl): ?>
																<img class="img-responsive img-ratio-object" src="<?= $featuredArticle->imageUrl ?>" alt="<?= $featuredArticle->translation->title ?>">
															<?php else: ?>
																<img class="img-responsive img-ratio-object" src="<?= Url::to('@web/img/announcement.jpg') ?>" alt="<?= $featuredArticle->translation->title ?>">
															<?php endif; ?>
														</figure>
														<div class="content-box">
															<div class="post-date"><h3><?= date('d', strtotime($featuredArticle->created_at)) ?><span><?= date('M', strtotime($featuredArticle->created_at)) ?>’<?= date('y', strtotime($featuredArticle->created_at)) ?></span></h3></div>
														</div>
													</div>
												</a>
												<div class="lower-content">
													<div class="equal">
														<div class="text mb-4">
															<h4><a class="article-title" href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $featuredArticle->translation->slug]) ?>"><?= $featuredArticle->translation->title ?></a></h4>
														</div>
														<p class="mb-4"><?= StringHelper::truncate(strip_tags($featuredArticle->translation->content), 400) ?></p>
													</div>
													<div class="links">
														<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $featuredArticle->translation->slug]) ?>" class="read-btn">
															<?= Yii::t('frontend', 'Read More') ?> <i class="flaticon-right-arrow"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4 col-md-12 col-sm-12 form-column">
						<div class="subscribe-inner centred">
							<div class="upper-box" style="background-image: url(<?= Url::to('@web/images/resource/schedule-1.jpg') ?>);">
								<div class="icon-box"><i class="flaticon-letter"></i></div>
								<h3><?= Yii::t('frontend', 'Subscribe To Newsletter') ?></h3>
							</div>
							<div class="lower-box">
								<?php $form = ActiveForm::begin([
									'id' => mb_strtolower($newsletterModel->formName()),
									'options' => [
										'novalidate' => true,
										'class' => 'subscribe-form',
									],
									'validateOnType' => true,
									'validateOnBlur' => false,
								]); ?>
								<div class="row">
									<div class="col-md-12 form-group">
										<?= $form->field($newsletterModel, 'last_name')->textInput([
											'class' => 'form-input form-control',
											'placeholder' => Yii::t('label', 'Last Name'),
										])->label(false) ?>
									</div>
									<div class="col-md-12 form-group">
										<?= $form->field($newsletterModel, 'first_name')->textInput([
											'class' => 'form-input form-control',
											'placeholder' => Yii::t('label', 'First Name'),
										])->label(false) ?>
									</div>
									<div class="col-md-12 form-group">
										<?= $form->field($newsletterModel, 'email')->textInput([
											'class' => 'form-input form-control',
											'placeholder' => Yii::t('label', 'Email'),
										])->label(false) ?>
									</div>
								</div>
								<?= $form->field($newsletterModel, 'workEmail', [
									'options' => [
										'class' => 'work-email',
									],
									'template' => '{input}',
								])->textarea(['required' => 'required'])->label(false) ?>
								<?php if (Yii::$app->settings->get('reCaptchaSiteKey', 'general')): ?>
									<div class="hidden g-recaptcha" data-sitekey="<?= Yii::$app->settings->get('reCaptchaSiteKey', 'general') ?>" data-badge="inline" data-size="invisible" data-callback="setResponse"></div>
									<?= $form->field($newsletterModel, 'captchaResponse', [
										'template' => '{input}',
									])->hiddenInput(['id' => 'captcha-response'])->label(false) ?>
								<?php endif; ?>
								<br>
								<?= Html::submitButton(Yii::t('common', 'Send') . ' <span><i class="fas fa-paper-plane"></i></span>', [
									'class' => 'theme-btn btn-block',
									'name' => 'newsletter-button',
								]) ?>
								<?php ActiveForm::end(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- schedules-section end -->
<?php endif; ?>

<?php if (!empty($members)): ?>
<!-- team-section -->
<section class="team-section sec-pad bg-color-1">
	<div class="pattern-layer" style="background-image: url(<?= Url::to('@web/images/shape/shape-5.png') ?>);"></div>
	<div class="auto-container">
		<div class="sec-title">
			<h2><?= Yii::t('frontend', 'Members') ?></h2>
			<div class="title-shape"></div>
			<?= Html::a(Yii::t('frontend', 'All members'), ['/member/default/index'], ['class' => 'theme-btn']) ?>
		</div>
		<div class="row clearfix">
			<?php 
			$delay = 0;
			foreach ($members as $member): 
			?>
		<div class="col-lg-4 col-md-6 col-sm-12 team-block">
			<div class="team-block-one wow fadeInUp animated animated" data-wow-delay="<?= $delay ?>ms" data-wow-duration="1500ms">
				<div class="inner-box">
					<figure class="image-box">
						<?php if (!empty($member->translation->content)): ?>
							<a href="<?= Url::to(['/member/default/view', 'slug' => $member->translation->slug]) ?>">
						<?php endif; ?>
							<?php if ($member->image): ?>
								<img src="<?= $member->imageUrl ?>" alt="<?= Html::encode($member->fullName) ?>">
							<?php else: ?>
								<img src="<?= Url::to('@web/img/avatar-placeholder.png') ?>" alt="<?= Html::encode($member->fullName) ?>">
							<?php endif; ?>
						<?php if (!empty($member->translation->content)): ?>
							</a>
						<?php endif; ?>
					</figure>
					<div class="lower-content">
						<div class="author-box">
							<h4>
								<?php if (!empty($member->translation->content)): ?>
									<a href="<?= Url::to(['/member/default/view', 'slug' => $member->translation->slug]) ?>">
								<?php endif; ?>
									<?= Html::encode($member->fullName) ?>
								<?php if (!empty($member->translation->content)): ?>
									</a>
								<?php endif; ?>
							</h4>
							<?php if (isset($member->translation->role)): ?>
								<span class="designation"><?= Html::encode($member->translation->role) ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
			<?php 
			$delay += 200;
			endforeach; 
			?>
		</div>
	</div>
</section>
<!-- team-section end -->
<?php endif; ?>

<?php
$this->registerJs('
        $( "#' . Html::getInputId($newsletterModel, 'workEmail') . '").removeAttr("required");
    ');
?>

<?php
$this->registerJs("
    $('#more').on('show.bs.collapse', function () {
        $('#toggleMoreBtn').text('" . Yii::t('frontend', 'Show Less') . " ▲');
    });

    $('#more').on('hide.bs.collapse', function () {
        $('#toggleMoreBtn').text('" . Yii::t('frontend', 'Show More') . " ▼');
    });
");
?>

<?php
$this->registerJs('
	var equalMaxHeight = 0;
	$(".equal").each(function(){
		if ($(this).height() > equalMaxHeight) { equalMaxHeight = $(this).height(); }
	});
	$(".equal").height(equalMaxHeight);
');
?>
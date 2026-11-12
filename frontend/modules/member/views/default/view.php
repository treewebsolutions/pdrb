<?php
/* @var $this \yii\web\View */
/* @var $model common\models\Member */
/* @var $modelTranslation common\models\MemberTranslation */

use common\models\Member;
use common\helpers\StringHelper;
use yii\helpers\Html;
use tws\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = [
	'label' => \common\models\Page::findPageByRoute(['/member/default/index'])->translation->title,
	'url' => ['index'],
	'template' => '<li>{link}</li>',
];
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
								Url::base(true),
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

<!-- member-details-section -->
<section class="sidebar-page-container sec-pad-2">
	<div class="auto-container">
		<div class="row clearfix">
			<div class="col-lg-12 col-md-12 col-sm-12 content-side">
				<div class="blog-details-content">
					<div class="inner-box">
						<div class="row clearfix">
							<?php if ($model->image): ?>
							<div class="col-lg-4 col-md-5 col-sm-12">
								<figure class="image-box" style="margin-bottom: 30px;">
									<img src="<?= $model->imageUrl ?>" alt="<?= Html::encode($model->fullName) ?>" style="width: 100%; border-radius: 5px;">
								</figure>
								<div class="author-info" style="text-align: center;">
									<h2><?= Html::encode($model->fullName) ?></h2>
									<?php if (isset($modelTranslation->role)): ?>
										<span class="designation" style="color: #e41e2f;"><?= Html::encode($modelTranslation->role) ?></span>
									<?php endif; ?>
									<?php if ($model->date_of_birth): ?>
										<p><strong><?= Yii::t('label', 'Date Of Birth') ?>:</strong> <?= Yii::$app->formatter->asDate($model->date_of_birth) ?></p>
									<?php endif; ?>
								</div>
							</div>
							<div class="col-lg-8 col-md-7 col-sm-12">
							<?php else: ?>
							<div class="col-lg-12 col-md-12 col-sm-12">
							<?php endif; ?>
								<div class="lower-content">
									<?php if (!$model->image): ?>
									<div class="author-info">
										<h2><?= Html::encode($model->fullName) ?></h2>
										<?php if (isset($modelTranslation->role)): ?>
											<span class="designation" style="color: #e41e2f;"><?= Html::encode($modelTranslation->role) ?></span>
										<?php endif; ?>
										<?php if ($model->date_of_birth): ?>
											<p><strong><?= Yii::t('label', 'Date Of Birth') ?>:</strong> <?= Yii::$app->formatter->asDate($model->date_of_birth) ?></p>
										<?php endif; ?>
									</div>
									<?php endif; ?>
									
									<?php if (isset($modelTranslation->description) && !empty($modelTranslation->description)): ?>
									<div class="text">
										<h4><?= Yii::t('frontend', 'About') ?></h4>
										<p><?= nl2br(Html::encode($modelTranslation->description)) ?></p>
									</div>
									<?php endif; ?>
									
									<?php if (isset($modelTranslation->content) && !empty($modelTranslation->content)): ?>
									<div class="text">
										<?= $modelTranslation->content ?>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Share Buttons -->
						<div class="share-option" id="share-section" style="margin-top: 30px; display: flex; align-items: center; justify-content: space-between;">
							<a href="#" class="share-icon" style="font-size: 24px;"><i class="flaticon-share"></i></a>
							<div style="display: flex; gap: 10px; align-items: center;">
								<a class="social-share" target="_blank" href="http://www.facebook.com/sharer.php?u={url}&picture={img}&title={title}&quote={desc}">
									<i class="fab fa-facebook-f"></i>
								</a>
								<a class="social-share" target="_blank" href="https://twitter.com/share?url={url}&text={title}&via={via}&hashtags={hashtags}">
									<i class="fab fa-twitter"></i>
								</a>
								<a class="social-share" target="_blank" href="http://www.linkedin.com/shareArticle?url={url}&title={title}">
									<i class="fab fa-linkedin-in"></i>
								</a>
								<a class="social-share" target="_blank" href="https://pinterest.com/pin/create/bookmarklet/?media={img}&url={url}&is_video={is_video}&description={title}">
									<i class="fab fa-pinterest-p"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- member-details-section end -->

<!-- Social Share Hidden Inputs -->
<input type="hidden" id="input_url" value="<?= Yii::$app->request->absoluteUrl ?>" />
<?php if ($model->image): ?>
	<input type="hidden" id="input_img" value="<?= rtrim(Yii::$app->urlManager->createAbsoluteUrl(['/']), '/\\') . $model->imageUrl ?>" />
<?php else: ?>
	<input type="hidden" id="input_img" value="<?= Url::to('@web/img/og-logo.jpg', true) ?>" />
<?php endif; ?>
<input type="hidden" id="input_title" value="<?= Html::encode($model->fullName) ?>" />
<input type="hidden" id="input_desc" value="<?= Html::encode(strip_tags(StringHelper::truncate($modelTranslation->description, 120))) ?>" />
<input type="hidden" id="input_app_id" value="" />
<input type="hidden" id="input_redirect_url" value="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>" />
<input type="hidden" id="input_via" value="" />
<input type="hidden" id="input_hashtags" value="" />


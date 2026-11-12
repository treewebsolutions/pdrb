<?php
/* @var $this \yii\web\View */
/* @var $model common\models\Article */
/* @var $model->translation common\models\ArticleTranslation */

use common\models\Article;
use common\models\ArticleCategory;
use common\models\File;
use common\models\Picture;
use common\widgets\ActiveForm;
use kartik\touchspin\TouchSpin;
use tws\widgets\carousel\Carousel;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = [
    'label' => \common\models\Page::findPageByRoute(['/activity/default/index'])->translation->title,
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
				<h2 class="article-breadcrumb-title"><?= \common\models\Page::findPageByRoute(['/activity/default/index'])->translation->title ?></h2>
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

<!-- sidebar-page-container -->
<section class="sidebar-page-container sec-pad-2">
	<div class="auto-container">
		<div class="row clearfix">
			<div class="col-lg-8 col-md-12 col-sm-12 content-side">
				<div class="blog-details-content">
					<div class="inner-box">
						<?php if ($model->video): ?>
							<figure class="image-box">
								<div class="embed-responsive embed-responsive-16by9">
									<iframe class="embed-responsive-item" src="<?= $model->getVideoEmbedUrl(true) ?>?hl=<?= Yii::$app->language ?>&amp;autoplay=0&amp;rel=0&amp;autohide=1&amp;showinfo=0&amp;iv_load_policy=3&amp;hd=1" allowfullscreen></iframe>
								</div>
								<div class="post-date">
									<h3><?= Yii::$app->formatter->asDate(strtotime($model->created_at), 'php:d') ?><span><?= Yii::$app->formatter->asDate(strtotime($model->created_at), 'php:M\'y') ?></span></h3>
								</div>
							</figure>
						<?php elseif ($model->imageUrl): ?>
							<figure class="image-box">
								<img src="<?= $model->imageUrl ?>" alt="<?= $model->translation->title ?>">
								<div class="post-date">
									<h3><?= Yii::$app->formatter->asDate(strtotime($model->created_at), 'php:d') ?><span><?= Yii::$app->formatter->asDate(strtotime($model->created_at), 'php:M\'y') ?></span></h3>
								</div>
							</figure>
						<?php endif; ?>
						<div class="lower-content">
							<?php if ($model->articleCategories && isset($model->articleCategories[0])): ?>
								<?php $slug = str_replace([Article::TYPE_BLOG, Article::TYPE_ACTIVITY], ['article', 'activity'], $model->type) ?>
								<div class="category"><a href="<?= Url::to(['/' . $slug . '/default/index', 'category' => $model->articleCategories[0]->translation->slug]) ?>"><i class="flaticon-star"></i><?= $model->articleCategories[0]->translation->title ?></a></div>
							<?php endif; ?>
						</div>
					<div class="text">
						<?= $model->translation->content ?>
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
					
					<?php if ($this->context->currentPage->translation->keywords): ?>
					<div class="tags-box">
						<ul class="tags-list clearfix">
							<li><?= Yii::t('label', 'Tags') ?>:</li>
							<?php $tags = explode(',', $this->context->currentPage->translation->keywords); ?>
							<?php foreach ($tags as $index => $tag): ?>
								<li><a href="<?= Url::to(['/activity/default/index', 'tag' => trim($tag)]) ?>"><?= trim($tag) ?><?= $index < count($tags) - 1 ? ',' : '' ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-12 col-sm-12 sidebar-side">
				<div class="blog-sidebar">
					<?php if ($articleCategories = ArticleCategory::findAllArticleCategories(ArticleCategory::TYPE_ACTIVITY)): ?>
					<div class="sidebar-widget category-widget">
						<div class="widget-title">
							<h3><?= Yii::t('frontend', 'Categories') ?></h3>
						</div>
						<div class="widget-content">
							<ul class="category-list clearfix">
								<?= ArticleCategory::treeview(null, null, ArticleCategory::STATUS_ACTIVE, ArticleCategory::TYPE_ACTIVITY) ?>
							</ul>
						</div>
					</div>
					<?php endif; ?>
					<?php if ($latestArticles = Article::findLatestArticles(5, Article::TYPE_ACTIVITY)): ?>
					<div class="sidebar-widget post-widget">
						<div class="widget-title">
							<h3><?= Yii::t('label', 'Latest Activities') ?></h3>
						</div>
						<div class="widget-content">
							<?php foreach ($latestArticles as $latestArticle): ?>
							<?php $slug = str_replace([Article::TYPE_BLOG, Article::TYPE_ACTIVITY], ['article', 'activity'], $latestArticle->type) ?>
							<div class="post">
								<?php if ($latestArticle->imageUrl): ?>
									<figure class="post-thumb">
										<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>">
											<img src="<?= $latestArticle->imageUrl ?>" alt="<?= $latestArticle->translation->title ?>">
										</a>
									</figure>
								<?php endif; ?>
								<h6><a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $latestArticle->translation->slug]) ?>"><?= $latestArticle->translation->title ?></a></h6>
								<p><i class="far fa-calendar"></i><?= Yii::$app->formatter->asDate($latestArticle->created_at, 'php:M d, Y'); ?></p>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- sidebar-page-container end -->

<!-- Social Share Hidden Inputs -->
<input type="hidden" id="input_url" value="<?= Yii::$app->request->absoluteUrl ?>" />
<?php if ($model->imageUrl): ?>
	<input type="hidden" id="input_img" value="<?= rtrim(Yii::$app->urlManager->createAbsoluteUrl(['/']), '/\\') . $model->imageUrl ?>" />
<?php else: ?>
	<input type="hidden" id="input_img" value="<?= Url::to('@web/img/og-logo.jpg', true) ?>" />
<?php endif; ?>
<input type="hidden" id="input_title" value="<?= $model->translation->title ?>" />
<input type="hidden" id="input_desc" value="<?= strip_tags(StringHelper::truncate($model->translation->content, 120)) ?>" />
<input type="hidden" id="input_app_id" value="" />
<input type="hidden" id="input_redirect_url" value="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>" />
<input type="hidden" id="input_via" value="" />
<input type="hidden" id="input_hashtags" value="" />



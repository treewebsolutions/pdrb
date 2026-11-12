<?php
/* @var $this \yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use common\models\Article;
use common\models\ArticleCategory;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\widgets\LinkPager;

if (Yii::$app->request->get('category') || Yii::$app->request->get('tag')) {
	$this->params['breadcrumbs'][] = [
		'label' => \common\models\Page::findPageByRoute(['/activity/default/index'])->translation->title,
		'url' => ['index'],
		'template' => '<li>{link}</li>',
	];
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

<!-- sidebar-page-container -->
<section class="sidebar-page-container sec-pad-2">
	<div class="auto-container">
		<div class="row clearfix">
			<?php if ($articles = $dataProvider->getModels()): ?>
			<div class="col-lg-8 col-md-12 col-sm-12 content-side">
				<div class="blog-classic-content">
					<div class="row clearfix">
						<?php foreach ($articles as $article): ?>
						<?php $slug = str_replace([Article::TYPE_BLOG, Article::TYPE_ACTIVITY], ['article', 'activity'], $article->type) ?>
						<div class="col-lg-6 col-md-6 col-sm-12">
							<div class="news-block-three">
								<div class="inner-box">
								<?php if ($article->video): ?>
									<figure class="image-box">
										<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $article->translation->slug]) ?>"><i class="fas fa-link"></i></a>
										<div class="embed-responsive embed-responsive-16by9">
											<iframe class="embed-responsive-item" src="<?= $article->getVideoEmbedUrl(true) ?>?hl=<?= Yii::$app->language ?>&amp;autoplay=0&amp;rel=0&amp;autohide=1&amp;showinfo=0&amp;iv_load_policy=3&amp;hd=1" allowfullscreen></iframe>
										</div>
									</figure>
								<?php elseif ($article->imageUrl): ?>
									<figure class="image-box">
										<a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $article->translation->slug]) ?>"><i class="fas fa-link"></i></a>
										<div class="embed-responsive embed-responsive-16by9">
											<img class="embed-responsive-item" src="<?= $article->imageUrl ?>" alt="<?= $article->translation->title ?>" style="object-fit: cover;">
										</div>
									</figure>
								<?php endif; ?>
									<div class="post-date">
										<h3><?= Yii::$app->formatter->asDate(strtotime($article->created_at), 'php:d') ?><span><?= Yii::$app->formatter->asDate(strtotime($article->created_at), 'php:M\'y') ?></span></h3>
									</div>
									<div class="lower-content">
										<?php if ($article->articleCategories && isset($article->articleCategories[0])): ?>
											<div class="category"><a href="<?= Url::to(['/' . $slug . '/default/index', 'category' => $article->articleCategories[0]->translation->slug]) ?>"><i class="flaticon-star"></i><?= $article->articleCategories[0]->translation->title ?></a></div>
										<?php endif; ?>
										<div class="equal">
											<h3><a class="color-blue" href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $article->translation->slug]) ?>"><?= $article->translation->title ?></a></h3>
											<p><?= StringHelper::truncate(strip_tags($article->translation->content), 200) ?></p>
										</div>
										<div class="btn-box"><a href="<?= Url::to(['/' . $slug . '/default/view', 'slug' => $article->translation->slug]) ?>"><?= Yii::t('frontend', 'Read More') ?></a></div>
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="pagination-wrapper">
						<?= LinkPager::widget([
							'pagination' => $dataProvider->pagination,
							'maxButtonCount' => 5,
							'registerLinkTags' => true,
							'firstPageLabel' => '<i class="far fa-angle-double-left"></i>',
							'prevPageLabel' => '<i class="far fa-angle-left"></i>',
							'nextPageLabel' => '<i class="far fa-angle-right"></i>',
							'lastPageLabel' => '<i class="far fa-angle-double-right"></i>',
							'options' => ['class' => 'pagination clearfix'],
							'linkOptions' => ['class' => ''],
							'activePageCssClass' => 'current',
							'disabledPageCssClass' => 'disabled',
						]) ?>
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
					<?php if ($this->context->currentPage->translation->keywords): ?>
					<div class="sidebar-widget tags-widget">
						<div class="widget-title">
							<h3><?= Yii::t('label', 'Tags') ?></h3>
						</div>
						<div class="widget-content">
							<ul class="tags-list clearfix">
								<?php $tags = explode(',', $this->context->currentPage->translation->keywords); ?>
								<?php foreach ($tags as $tag): ?>
									<li><a href="<?= Url::to(['/activity/default/index', 'tag' => trim($tag)]) ?>"><?= trim($tag) ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php else: ?>
				<div class="col-lg-12">
					<p class="text-center">
						<?= Yii::t('common', 'No records found.') ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<!-- sidebar-page-container end -->

<?php
$this->registerJs('
	var equalMaxHeight = 0;
	$(".equal").each(function(){
		if ($(this).height() > equalMaxHeight) { equalMaxHeight = $(this).height(); }
	});
	$(".equal").height(equalMaxHeight);
');
?>



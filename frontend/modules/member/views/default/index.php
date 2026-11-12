<?php
/* @var $this \yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use common\models\Member;
use common\helpers\StringHelper;
use yii\helpers\Html;
use tws\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\widgets\LinkPager;

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

<!-- team-section -->
<section class="team-section sec-pad">
	<div class="auto-container">
		<?php if ($members = $dataProvider->getModels()): ?>
		<div class="row clearfix">
			<?php 
			$delay = 0;
			foreach ($members as $member): 
			?>
		<div class="col-lg-4 col-md-6 col-sm-12 team-block">
			<div class="team-block-one wow fadeInUp animated" data-wow-delay="<?= $delay ?>ms" data-wow-duration="1500ms">
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
			$delay += 100;
			if ($delay >= 800) $delay = 0; // Reset animation delay after 8 items
			endforeach; 
			?>
		</div>
		
		<!-- Pagination -->
		<div class="pagination-wrapper text-center">
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
		<?php else: ?>
		<div class="row clearfix">
			<div class="col-lg-12">
				<p class="text-center">
					<?= Yii::t('common', 'No records found.') ?>
				</p>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
<!-- team-section end -->


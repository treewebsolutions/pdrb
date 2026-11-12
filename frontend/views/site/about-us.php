<?php
/* @var $this yii\web\View */

use common\helpers\StringHelper;
use common\models\Member;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = Html::encode($this->title);

// Get members
$members = Member::findAllMembers(null, Member::TYPE_FOUNDER);
?>

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

<section class="about-style-three">
	<div class="auto-container">
		<div class="row">
			<div class="col-lg-12">
				<?= $this->context->currentPage->content ?>
			</div>
		</div>
	</div>
</section>

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
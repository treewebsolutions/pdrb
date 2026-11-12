<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = Html::encode($this->title);
?>

<section class="blog">

	<div class="pagetitle wow fadeInUp">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1><?= $this->title ?></h1>
					<div class="breadcrumb">
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
									'template' => '<li>{link}</li><li>/</li>',
								],
								'options' => [
									'class' => '',
								],
								'encodeLabels' => false,
								'links' => !empty($breadcrumbs) ? $breadcrumbs : [],
							]) ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="blogpost">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<?= $this->context->currentPage->content ?>
				</div>
			</div>
		</div>
	</div>

</section>

<!-- Practice Section -->
<div id="gallery-section" class="practice-section container-fluid no-padding">
	<div class="section-padding"></div>
	<div class="container">
		<div class="col-12">
			<?= $this->context->currentPage->content ?>
		</div>
		<?php if ($pictures = $dataProvider->getModels()): ?>
			<div class="photos">
				<?php foreach ($pictures as $picture): ?>
					<?php if ($picture->image && is_file(Yii::getAlias("@uploads/picture/{$picture->id}/{$picture->image}"))): ?>
						<a href="<?= $picture->imageUrl ?>" data-fancybox="pictures" data-caption="<?= $picture->translation->title ?>"><img src="<?= $picture->imageUrl ?>" alt="<?= $picture->translation->title ?>" style="margin-bottom: 10px;"></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="section-padding"></div>
</div><!-- Practice Section /- -->

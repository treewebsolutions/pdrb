<?php
/* @var $this yii\web\View */
/* @var $branches common\models\Branch[] */

use common\models\Country;
use tws\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = Html::encode($this->title);
$countries = $branches ? Country::findAllCountries() : [];
?>

<!-- Page Title -->
<section class="page-title" style="background-image: url(<?= $this->context->currentPage && $this->context->currentPage->cover ? $this->context->currentPage->getCoverUrl(true) : Url::to('@web/img/banner.jpg') ?>);">
	<div class="auto-container">
		<div class="content-box">
			<div class="title centred">
				<h1><?= $this->title ?></h1>
			</div>
			<ul class="bread-crumb clearfix">
				<?php if ($breadcrumbs = $this->params['breadcrumbs']): ?>
					<?php
					$lastBreadcrumb = array_pop($breadcrumbs);
					$breadcrumbs[] = [
						'label' => $lastBreadcrumb,
						'template' => '<li class="active">{link}</li>',
					];
					$homePage = \common\models\Page::findPageByRoute(['site/index']);
					?>
					<?= Breadcrumbs::widget([
						'homeLink' => [
							'label' => '<i class="fa fa-home"></i>',
							'url' => implode('', array_filter([
								Url::base(true),
								(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
							])),
							'title' => $homePage ? $homePage->translation->title : null,
							'template' => '<li>{link}</li>',
						],
						'options' => [
							'class' => '',
						],
						'encodeLabels' => false,
						'links' => $breadcrumbs,
					]) ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</section>
<!-- End Page Title -->

<section class="contact-information branch-list">
	<div class="auto-container">
		<?php if (empty($branches)): ?>
			<p class="text-center"><?= Yii::t('frontend', 'No records found.') ?></p>
		<?php endif; ?>

		<?php foreach ($branches as $branch): ?>
			<?php
			$country = $branch->country ? ($countries[$branch->country] ?? null) : null;
			$address = implode(', ', array_filter([
				$branch->street_name,
				$branch->street_number,
				$branch->locality,
				$branch->zip_code,
				$branch->county,
				$country ? $country->translation->name : $branch->country,
			])) ?: $branch->address;
			$phones = array_values(array_filter(array_map('trim', array_merge(
				explode(',', (string) $branch->mobile_phone),
				explode(',', (string) $branch->fixed_phone)
			))));
			$schedule = $branch->getScheduleText();
			$hasCoordinates = $branch->latitude !== null && $branch->latitude !== '' && $branch->longitude !== null && $branch->longitude !== '';
			$mapQuery = $hasCoordinates ? "{$branch->latitude},{$branch->longitude}" : $address;
			?>
			<div class="row clearfix branch-item" id="branch-<?= $branch->id ?>">
				<div class="col-md-6 col-sm-12 content-column">
					<div class="content_block_13">
						<div class="content-box">
							<h3 class="branch-title"><?= Html::encode($branch->name) ?></h3>

							<?php if ($branch->contactName): ?>
								<div class="single-item">
									<div class="icon-box"><i class="fa fa-user"></i></div>
									<ul class="info clearfix">
										<li>
											<h5><?= Yii::t('common', 'Contact') ?></h5>
											<p><?= Html::encode($branch->contactName) ?></p>
										</li>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ($phones): ?>
								<div class="single-item">
									<div class="icon-box"><i class="flaticon-emergency-call"></i></div>
									<ul class="info clearfix">
										<?php foreach ($phones as $index => $phone): ?>
											<li>
												<h5><?= $index == 0 ? Yii::t('label', 'Phone') : Yii::t('label', 'Alternative Phone') ?></h5>
												<p><?= Html::a(Html::encode($phone), 'tel:' . preg_replace('/[^\d+]/', '', $phone), ['data-prevent-page-overlay' => 'true']) ?></p>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ($branch->email): ?>
								<div class="single-item">
									<div class="icon-box"><i class="flaticon-mail-inbox-app"></i></div>
									<ul class="info clearfix">
										<li>
											<h5><?= Yii::t('label', 'Email') ?></h5>
											<p><?= Html::a(Html::encode($branch->email), 'mailto:' . $branch->email, ['data-prevent-page-overlay' => 'true']) ?></p>
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
												<?php if ($hasCoordinates): ?>
													<?= Html::a(Html::encode($address), "https://www.google.com/maps/place/{$branch->latitude},{$branch->longitude}", ['target' => '_blank', 'rel' => 'noopener']) ?>
												<?php else: ?>
													<?= Html::encode($address) ?>
												<?php endif; ?>
											</p>
										</li>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ($schedule): ?>
								<div class="single-item">
									<div class="icon-box"><i class="flaticon-clock"></i></div>
									<ul class="info clearfix">
										<li>
											<h5><?= Yii::t('label', 'Working Hours') ?></h5>
											<p><?= nl2br(Html::encode($schedule)) ?></p>
										</li>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ($branch->fax): ?>
								<div class="single-item">
									<div class="icon-box"><i class="flaticon-fax"></i></div>
									<ul class="info clearfix">
										<li>
											<h5><?= Yii::t('label', 'Fax') ?></h5>
											<p><?= Html::a(Html::encode($branch->fax), 'tel:' . preg_replace('/[^\d+]/', '', $branch->fax), ['data-prevent-page-overlay' => 'true']) ?></p>
										</li>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ($branch->company): ?>
								<div class="single-item">
									<div class="icon-box"><i class="flaticon-apartment"></i></div>
									<ul class="info clearfix">
										<li>
											<h5><?= Yii::t('label', 'Company Information') ?></h5>
											<p>
												<?= implode('<br>', array_map([Html::class, 'encode'], array_filter([
													$branch->company,
													$branch->tin ? Yii::t('label', 'Taxpayer Identification Number') . ': ' . $branch->tin : '',
													$branch->registration_number ? Yii::t('label', 'Registration Number') . ': ' . $branch->registration_number : '',
												]))) ?>
											</p>
										</li>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-sm-12 map-column">
					<?php if ($mapQuery): ?>
						<div class="branch-map">
							<iframe
								src="https://maps.google.com/maps?q=<?= rawurlencode($mapQuery) ?>&z=15&hl=<?= Html::encode(mb_substr(Yii::$app->language, 0, 2)) ?>&output=embed"
								title="<?= Html::encode($branch->name) ?>"
								loading="lazy"
								referrerpolicy="no-referrer-when-downgrade"
								allowfullscreen></iframe>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php
$this->registerCss('
	.branch-list .branch-item { padding-bottom: 40px; margin-bottom: 40px; border-bottom: 1px solid #e5e5e5; }
	.branch-list .branch-item:last-child { border-bottom: 0; margin-bottom: 0; }
	.branch-list .branch-title { margin-bottom: 25px; }
	.branch-list .branch-map { position: relative; width: 100%; height: 100%; min-height: 400px; }
	.branch-list .branch-map iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
	@media (max-width: 991px) { .branch-list .map-column { margin-top: 20px; } }
');
?>

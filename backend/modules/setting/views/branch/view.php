<?php
/* @var $this yii\web\View */
/* @var $model common\models\Branch */

use common\models\Branch;
use common\models\Country;
use common\models\Language;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('common', 'View {item}', ['item' => Yii::t('common', 'Branch')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Branches'),
		'url' => ['index'],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewBranch'),
		'tag' => 'a',
		'url' => ['index'],
		'icon' => 'fa fa-list',
		'options' => [
			'class' => 'btn btn-sm btn-default',
			'title' => Yii::t('common', 'List'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('updateBranch'),
		'tag' => 'a',
		'url' => ['update', 'id' => $model->id],
		'icon' => 'fa fa-edit',
		'options' => [
			'class' => 'btn btn-sm btn-primary',
			'title' => Yii::t('common', 'Update'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('deleteBranch'),
		'tag' => 'a',
		'url' => ['delete', 'id' => $model->id],
		'icon' => 'fa fa-trash',
		'options' => [
			'class' => 'btn btn-sm btn-danger',
			'title' => Yii::t('common', 'Delete'),
			'data' => [
				'toggle' => 'tooltip',
				'confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
				'method' => 'POST',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('createBranch'),
		'tag' => 'a',
		'url' => ['create'],
		'icon' => 'fa fa-plus',
		'options' => [
			'class' => 'btn btn-sm btn-success',
			'title' => Yii::t('common', 'Create'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
];

$dash = '&mdash;';
$text = function ($value) use ($dash) {
	return ($value !== null && $value !== '') ? Html::encode($value) : $dash;
};
$schedule = $model->getScheduleTranslations();
$scheduleHtml = [];
foreach (Language::findAllLanguages() as $language) {
	if (!empty($schedule[$language->language_id])) {
		$scheduleHtml[] = Html::tag('strong', mb_strtoupper($language->language)) . ': ' . nl2br(Html::encode($schedule[$language->language_id]));
	}
}
?>

<div class="table-responsive">
	<?= DetailView::widget([
		'model' => $model,
		'options' => [
			'class' => 'table table-striped table-bordered detail-view detail-view-fixed',
		],
		'attributes' => [
			['format' => 'raw', 'attribute' => 'name', 'value' => $text($model->name)],
			['format' => 'raw', 'label' => Yii::t('common', 'Contact'), 'value' => $text($model->contactName)],
			['format' => 'raw', 'attribute' => 'company', 'value' => $text($model->company)],
			['format' => 'raw', 'attribute' => 'tin', 'value' => $text($model->tin)],
			['format' => 'raw', 'attribute' => 'registration_number', 'value' => $text($model->registration_number)],
			['format' => 'raw', 'attribute' => 'email', 'value' => $model->email ? Html::a(Html::encode($model->email), 'mailto:' . $model->email) : $dash],
			['format' => 'raw', 'attribute' => 'mobile_phone', 'value' => $model->mobile_phone ? Html::a(Html::encode($model->mobile_phone), 'tel:' . $model->mobile_phone) : $dash],
			['format' => 'raw', 'attribute' => 'fixed_phone', 'value' => $model->fixed_phone ? Html::a(Html::encode($model->fixed_phone), 'tel:' . $model->fixed_phone) : $dash],
			['format' => 'raw', 'attribute' => 'fax', 'value' => $text($model->fax)],
			['format' => 'raw', 'attribute' => 'schedule', 'value' => $scheduleHtml ? implode('<br>', $scheduleHtml) : $dash],
			['format' => 'raw', 'attribute' => 'address', 'value' => $text($model->address)],
			['format' => 'raw', 'attribute' => 'street_name', 'value' => $text($model->street_name)],
			['format' => 'raw', 'attribute' => 'street_number', 'value' => $text($model->street_number)],
			['format' => 'raw', 'attribute' => 'locality', 'value' => $text($model->locality)],
			['format' => 'raw', 'attribute' => 'zip_code', 'value' => $text($model->zip_code)],
			['format' => 'raw', 'attribute' => 'county', 'value' => $text($model->county)],
			[
				'format' => 'raw',
				'attribute' => 'country',
				'value' => function (Branch $model) use ($dash) {
					if ($model->country) {
						$country = Country::findAllCountries()[$model->country] ?? null;
						return $country ? $country->translation->name : Html::encode($model->country);
					}
					return $dash;
				},
			],
			[
				'format' => 'raw',
				'label' => Yii::t('label', 'Latitude') . ' / ' . Yii::t('label', 'Longitude'),
				'value' => ($model->latitude && $model->longitude)
					? Html::a(Html::encode("{$model->latitude}, {$model->longitude}"), "https://www.google.com/maps?q={$model->latitude},{$model->longitude}", ['target' => '_blank', 'rel' => 'noopener'])
					: $dash,
			],
			['format' => 'raw', 'attribute' => 'sort_order', 'value' => $text($model->sort_order)],
			[
				'format' => 'raw',
				'attribute' => 'created_by',
				'value' => $model->creator ? Html::a($model->creator->getFullName(), ['/user-manager/user/view', 'id' => $model->creator->id]) : $dash,
			],
			['format' => 'raw', 'attribute' => 'created_at', 'value' => $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : $dash],
			[
				'format' => 'raw',
				'attribute' => 'updated_by',
				'value' => $model->updater ? Html::a($model->updater->getFullName(), ['/user-manager/user/view', 'id' => $model->updater->id]) : $dash,
			],
			['format' => 'raw', 'attribute' => 'updated_at', 'value' => $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : $dash],
			[
				'format' => 'raw',
				'attribute' => 'status',
				'value' => function (Branch $model) {
					$status = Branch::getStatusLabels()[$model->status];
					return Html::tag('span', $status['label'], ['class' => 'label label-' . $status['color']]);
				},
			],
		],
	]) ?>
</div>

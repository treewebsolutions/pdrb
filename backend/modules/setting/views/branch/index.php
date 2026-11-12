<?php
/* @var $this yii\web\View */

use common\models\Branch;
use common\widgets\datatable\DataTable;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('common', 'Branches');
$this->params['breadcrumbs'] = [
	$this->title,
];

if ($showTrash = Yii::$app->settings->get('enableSoftDelete')) {
	$showTrash = Yii::$app->request->get('deleted') == Branch::YES;
	$this->params['breadcrumbs'][] = [
		'template' => '<li class="page-links">{link}</li>',
		'label' => implode('', [
			Html::a(Yii::t('common', 'Current'), ['index'], ['class' => $showTrash ? '' : 'active']),
			Html::a(Yii::t('common', 'Trash'), ['index', 'deleted' => Branch::YES], ['class' => $showTrash ? 'active' : '']),
		]),
	];
}

$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('restoreBranch') && $showTrash,
		'tag' => 'button',
		'icon' => 'fa fa-undo',
		'options' => [
			'class' => 'btn btn-sm btn-success hidden',
			'title' => Yii::t('common', 'Restore'),
			'data' => [
				'toggle' => 'tooltip',
				'dt-bulk-operation' => 'restore',
				'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
				'dt-url' => Url::to(['restore']),
				'dt-table' => '#dt-branches',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('deleteBranch'),
		'tag' => 'button',
		'icon' => 'fa fa-trash',
		'options' => [
			'class' => 'btn btn-sm btn-danger hidden',
			'title' => $showTrash ? Yii::t('common', 'Delete Permanently') : Yii::t('common', 'Delete'),
			'data' => [
				'toggle' => 'tooltip',
				'dt-bulk-operation' => $showTrash ? 'delete-permanently' : 'delete',
				'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
				'dt-url' => Url::to(['delete']),
				'dt-table' => '#dt-branches',
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
?>

<div class="dt-scroll-x">
<?= DataTable::widget([
	'id' => 'dt-branches',
	'options' => [
		'class' => 'table table-bordered table-hover',
	],
	'showColumnFilters' => true,
	'clientOptions' => [
		'deferRender' => true,
		'processing' => true,
		'serverSide' => true,
		'ajax' => [
			'url' => Url::to(['dt-branches']),
			'method' => 'POST',
			'data' => new JsExpression('function (data) {
				data.deleted = ' . json_encode($showTrash ? Branch::YES : null) . ';
			}'),
		],
		'order' => [
			[2, 'asc'],
		],
		'pageLength' => Yii::$app->settings->get('itemsPerPage'),
		'lengthMenu' => [
			'autoCreate' => true,
			'displayAll' => Yii::t('common', 'All'),
		],
		'autoWidth' => true,
		'responsive' => false,
		'scrollX' => false,
		'colReorder' => true,
		'dom' => 'Blfrtip',
		'buttons' => [
			[
				'extend' => 'colvis',
				'text' => '<span class="fa fa-eye"></span>',
			],
		],
		'columns' => [
			[
				'class' => 'common\widgets\datatable\CheckboxColumn',
			],
			[
				'class' => 'common\widgets\datatable\ActionColumn',
				'data' => 'action',
				'title' => Yii::t('common', 'Action'),
			],
			[
				'data' => 'sort_order',
				'className' => 'col-autowidth',
				'title' => '#',
				'filter' => ['text'],
			],
			[
				'data' => 'name',
				'title' => Yii::t('label', 'Name'),
				'filter' => ['text'],
			],
			[
				'data' => 'contact_name',
				'title' => Yii::t('common', 'Contact'),
				'filter' => ['text'],
			],
			[
				'data' => 'email',
				'title' => Yii::t('label', 'Email'),
				'filter' => ['text'],
			],
			[
				'data' => 'mobile_phone',
				'title' => Yii::t('label', 'Mobile Phone'),
				'filter' => ['text'],
			],
			[
				'data' => 'fixed_phone',
				'title' => Yii::t('label', 'Fixed Phone'),
				'filter' => ['text'],
			],
			[
				'data' => 'address',
				'title' => Yii::t('label', 'Address'),
				'filter' => ['text'],
			],
			[
				'data' => 'locality',
				'title' => Yii::t('label', 'City'),
				'filter' => ['text'],
			],
			[
				'data' => 'county',
				'title' => Yii::t('label', 'County'),
				'filter' => ['text'],
			],
			[
				'data' => 'created_at',
				'title' => Yii::t('label', 'Created At'),
				'filter' => ['date', 'icu:' . Yii::$app->settings->get('dateFormat')],
			],
			[
				'data' => 'status',
				'title' => Yii::t('label', 'Status'),
				'className' => 'col-autowidth',
				'filter' => ['select', ArrayHelper::getColumn(Branch::getStatusLabels(), 'label')],
			],
		],
	],
]) ?>
</div>

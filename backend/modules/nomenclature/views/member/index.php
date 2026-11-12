<?php
/* @var $this yii\web\View */

use backend\modules\export\widgets\export\Export;
use backend\modules\nomenclature\models\MemberSearch;
use common\models\Member;
use common\models\Membership;
use common\models\Team;
use common\widgets\datatable\DataTable;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('common', 'Members');
$this->params['breadcrumbs'] = [
	$this->title,
];

if ($showTrash = Yii::$app->settings->get('enableSoftDelete')) {
	$showTrash = Yii::$app->request->get('deleted') == Member::YES;
	$this->params['breadcrumbs'][] = [
		'template' => '<li class="page-links">{link}</li>',
		'label' => implode('', [
			Html::a(Yii::t('common', 'Current'), ['index'], ['class' => $showTrash ? '' : 'active']),
			Html::a(Yii::t('common', 'Trash'), ['index', 'deleted' => Member::YES], ['class' => $showTrash ? 'active' : '']),
		]),
	];
}

$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('restoreMember') && $showTrash,
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
				'dt-table' => '#dt-members',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('deleteMember'),
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
				'dt-table' => '#dt-members',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('createMember'),
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
	[
		'visible' => Yii::$app->user->can('viewMember'),
		'tag' => 'a',
		'url' => ['import'],
		'icon' => 'fa fa-upload',
		'options' => [
			'class' => 'btn btn-sm btn-info',
			'title' => Yii::t('common', 'Import'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => true,
		'content' => Export::widget([
			'buttonOptions' => [
				'class' => 'btn btn-sm btn-default',
				'style' => 'margin: 0 5px;',
				'title' => Yii::t('common', 'Export'),
				'label' => '<span class="fa fa-file-excel-o"></span>',
			],
			'dropdownOptions' => [
				'class' => 'pull-right',
			],
			'items' => [
				Export::FORMAT_CSV => [
					'visible' => false,
					'label' => 'CSV',
					'url' => '#',
					'clientOptions' => [
						'fieldDelimiter' => ',',
						'fieldEnclosure' => '"',
						'shouldAddBom' => true,
					],
				],
				Export::FORMAT_XLSX => [
					'visible' => true,
					'label' => 'XLSX',
					'url' => '#',
					'clientOptions' => [
						'shouldCreateNewSheetsAutomatically' => true,
						'shouldUseInlineStrings' => true,
					],
				],
				Export::FORMAT_PDF => [
					'visible' => false,
					'label' => 'PDF',
					'url' => '#',
					'clientOptions' => [
						'allowHtml' => false,
					],
				],
			],
			'clientOptions' => [
				'url' => Url::to(['export']),
				'dataTable' => '#dt-members',
				'model' => MemberSearch::class,
				'title' => Yii::t('common', 'Members'),
			],
		]),
	],
];
?>

<div class="dt-scroll-x">
<?= DataTable::widget([
	'id' => 'dt-members',
	'options' => [
		'class' => 'table table-bordered table-hover',
	],
	'showColumnFilters' => true,
	'clientOptions' => [
		'deferRender' => true,
		'processing' => true,
		'serverSide' => true,
		'ajax' => [
			'url' => Url::to(['dt-members']),
			'method' => 'POST',
			'data' => new JsExpression('function (data) {
				data.deleted = ' . json_encode($showTrash ? Member::YES : null) . ';
			}'),
		],
		'order' => [
			[2, 'desc'],
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
				'data' => 'figure',
				'title' => Yii::t('label', 'Figure'),
				'className' => 'col-autowidth text-center',
				'orderable' => false,
				'filter' => false,
			],
			[
				'data' => 'first_name',
				'title' => Yii::t('label', 'First Name'),
				'filter' => ['text'],
			],
			[
				'data' => 'middle_name',
				'title' => Yii::t('label', 'Middle Name'),
				'filter' => ['text'],
			],
			[
				'data' => 'last_name',
				'title' => Yii::t('label', 'Last Name'),
				'filter' => ['text'],
			],
			[
				'data' => 'email',
				'title' => Yii::t('label', 'Email'),
				'filter' => ['text'],
			],
			[
				'data' => 'phone',
				'title' => Yii::t('label', 'Phone'),
				'filter' => ['text'],
			],
			[
				'data' => 'date_of_birth',
				'title' => Yii::t('label', 'Date Of Birth'),
				'filter' => ['date', 'icu:' . Yii::$app->settings->get('dateFormat')],
			],
			[
				'data' => 'address',
				'title' => Yii::t('label', 'Address'),
				'filter' => ['text'],
			],
			[
				'data' => 'locality',
				'title' => Yii::t('label', 'Locality'),
				'filter' => ['text'],
			],
			[
				'data' => 'country',
				'title' => Yii::t('label', 'Country'),
				'filter' => ['text'],
			],
			[
				'data' => 'type',
				'title' => Yii::t('label', 'Type'),
				'filter' => ['select', Member::getTypeLabels()],
			],
			[
				'data' => 'membership',
				'title' => Yii::t('common', 'Membership'),
				'filter' => ['select', Membership::getTypeLabels()],
				'orderable' => false,
			],
			[
				'data' => 'role',
				'title' => Yii::t('label', 'Role'),
				'filter' => ['text'],
			],
			[
				'data' => 'created_by',
				'title' => Yii::t('label', 'Created By'),
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
				'filter' => ['select', ArrayHelper::getColumn(Member::getStatusLabels(), 'label')],
			],
		],
	],
]) ?>
</div>

<?php
$this->registerJs('
		$("#dt-members").on("draw.dt", function () {
	        $("#dt-members").parent().doubleScroll();
			$(".doubleScroll-scroll").width($("#dt-members").outerWidth());
		});
		$("#dt-members").on("column-visibility.dt", function () {
	        $("#dt-members").parent().doubleScroll();
			$(".doubleScroll-scroll").width($("#dt-members").outerWidth());
		});
		$(window).resize(function () {
	        $("#dt-members").parent().doubleScroll();
			$(".doubleScroll-scroll").width($("#dt-members").outerWidth());
		});
	');
?>

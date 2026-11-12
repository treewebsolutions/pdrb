<?php
/* @var $this yii\web\View */
/* @var $model common\models\Member */

use common\models\EventLog;
use common\models\Member;
use common\widgets\datatable\DataTable;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\DetailView;

$this->title = Yii::t('common', 'View {item}', ['item' => Yii::t('common', 'Member')]);
$this->params['breadcrumbs'] = [
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewMember'),
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
		'visible' => Yii::$app->user->can('updateMember'),
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
		'visible' => Yii::$app->user->can('deleteMember'),
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
];

$showEventLogs = isset($showEventLogs) ? $showEventLogs : Yii::$app->eventLog->enabled;
?>

<div class="table-responsive">
	<?= DetailView::widget([
		'model' => $model,
		'options' => [
			'class' => 'table table-striped table-bordered detail-view detail-view-fixed',
		],
		'attributes' => [
			[
				'format' => 'raw',
				'label' => Yii::t('label', 'Image'),
				'value' => function (Member $model) {
					if ($model->image) {
						$imgTag = Html::img($model->getImageUrl(), [
							'class' => 'img-responsive',
							'alt' => $model->translation->role,
						]);
						return Html::a($imgTag, $model->getImageUrl(), [
							'class' => 'gallery-thumbnail',
							'title' => Yii::t('common', 'Open Gallery'),
							'data' => [
								'toggle' => 'tooltip',
								'fancybox' => 'members',
								'caption' => $model->translation->role,
							],
						]);
					}
					return '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Name'),
				'value' => function (Member $model) {
					return $model->fullName ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Email'),
				'value' => function (Member $model) {
					return $model->email ? Html::a($model->email, 'mailto:' . $model->email) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Phone'),
				'value' => function (Member $model) {
					return $model->phone ? Html::a($model->phone, 'tel:' . $model->phone) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Date Of Birth'),
				'value' => function (Member $model) {
					return $model->date_of_birth ? Yii::$app->formatter->asDate($model->date_of_birth) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Address'),
				'value' => function (Member $model) {
					return $model->address ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Locality'),
				'value' => function (Member $model) {
					return $model->locality ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Country'),
				'value' => function (Member $model) {
					if ($model->country) {
						$country = \common\models\Country::findAllCountries()[$model->country] ?? null;
						return $country ? $country->translation->name : $model->country;
					}
					return '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Type'),
				'value' => function (Member $model) {
					$type = Member::getTypeLabels()[$model->type] ?? null;
					return $type ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Role'),
				'value' => function (Member $model) {
					return $model->translation->role ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Keywords'),
				'value' => function (Member $model) {
					return $model->translation->keywords ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Description'),
				'value' => function (Member $model) {
					return $model->translation->description ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Content'),
				'value' => function (Member $model) {
					return $model->translation->content ? Html::tag('div', $model->translation->content, ['class' => 'small-preview-container']) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Sort Order'),
				'value' => function (Member $model) {
					return $model->sort_order ?: '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Created By'),
				'value' => function (Member $model) {
					return $model->creator ? Html::a($model->creator->getFullName(), ['/user-manager/user/view', 'id' => $model->creator->id]) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Created At'),
				'value' => function (Member $model) {
					return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Updated By'),
				'value' => function (Member $model) {
					return $model->updater ? Html::a($model->updater->getFullName(), ['/user-manager/user/view', 'id' => $model->updater->id]) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Updated At'),
				'value' => function (Member $model) {
					return $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '&mdash;';
				},
			],
			[
				'format' => 'html',
				'label' => Yii::t('label', 'Status'),
				'value' => function (Member $model) {
					$status = Member::getStatusLabels()[$model->status];
					return Html::tag('span', $status['label'], ['class' => 'label label-' . $status['color']]);
				},
			],
		],
	]) ?>
</div>

<?php if ((!isset($showEventLogs) || $showEventLogs === true) && Yii::$app->user->can('viewEventLog')) : ?>
	<div class="portlet box blue-hoki mt-15">
		<div class="portlet-title">
			<div class="caption"><?= Yii::t('common', 'Event Logs') ?></div>
		</div>
		<div class="portlet-body">
			<?= DataTable::widget([
				'id' => 'dt-logs',
				'options' => [
					'class' => 'table table-bordered table-hover',
				],
				'showColumnFilters' => true,
				'clientOptions' => [
					'deferRender' => true,
					'processing' => true,
					'serverSide' => true,
					'ajax' => [
						'url' => Url::to(['/eventlog-manager/event-log/dt-event-logs']),
						'method' => 'POST',
						'data' => new JsExpression('function (data) {
							data.model = ' . json_encode(Member::class) . '; 
							data.model_key = "' . $model->id . '";
						}'),
						'reloadInterval' => 5 * 60000,
					],
					'order' => [
						[3, 'desc'],
					],
					'pageLength' => Yii::$app->settings->get('itemsPerPage'),
					'lengthMenu' => [
						'autoCreate' => true,
						'displayAll' => Yii::t('common', 'All'),
					],
					'autoWidth' => false,
					'responsive' => true,
					'columns' => [
						[
							'class' => 'common\widgets\datatable\ActionColumn',
							'title' => Yii::t('common', 'Action'),
							'buttons' => [
								'event-log' => [
									'visible' => Yii::$app->user->can('viewEventLog'),
									'url' => ['/eventlog-manager/event-log/view', 'id' => new JsExpression('id')],
									'content' => '<span class="fa fa-eye"></span>',
									'options' => [
										'class' => 'action-view btn btn-xs btn-info',
										'title' => Yii::t('common', 'View'),
										'data' => [
											'toggle' => 'tooltip',
											'popup-action' => '',
										],
									],
								],
							],
						],
						[
							'data' => 'user',
							'title' => Yii::t('label', 'User'),
							'filter' => ['text'],
						],
						[
							'data' => 'operation',
							'title' => Yii::t('label', 'Operation'),
							'filter' => ['select', ArrayHelper::getColumn(EventLog::getActions(), 'label')],
						],
						[
							'data' => 'date',
							'title' => Yii::t('label', 'Date'),
							'filter' => ['date', 'icu:' . Yii::$app->settings->get('dateFormat')],
						],
						[
							'data' => 'ip_address',
							'title' => Yii::t('label', 'IP Address'),
							'filter' => ['text'],
						],
					],
				],
			]) ?>
		</div>
	</div>
<?php endif; ?>

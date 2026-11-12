<?php

namespace backend\modules\nomenclature\models;

use common\helpers\DateHelper;
use common\models\Country;
use common\models\Member;
use common\models\MemberTranslation;
use common\models\Membership;
use common\widgets\datatable\DataTableAction;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class MemberSearch extends DataTableAction
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->query = Member::find()
			->alias('m')
			->select([
				'm.id',
				'm.image',
				'm.first_name',
				'm.middle_name',
				'm.last_name',
				'm.email',
				'm.phone',
				'm.date_of_birth',
				'm.address',
				'm.locality',
				'm.country',
				'm.type',
				'm.sort_order',
				'm.created_by',
				'm.updated_by',
				'm.created_at',
				'm.updated_at',
				'm.status',
			])
			->joinWith([
				'memberTranslations mt' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'mt.language_id' => Yii::$app->language,
						'mt.deleted' => MemberTranslation::NO,
					]);
				},
				'creator cr' => function (ActiveQuery $query) {
					$query->select([
						'cr.id',
						'cr.first_name',
						'cr.middle_name',
						'cr.last_name',
					]);
				},
			])
			->with(['activeMembership'])
			->andWhere([
				'm.deleted' => isset($this->requestParams['deleted']) ? $this->requestParams['deleted'] : Member::NO,
			]);
	}

	/**
	 * @inheritdoc
	 *
	 * Uses a join-free COUNT for recordsTotal (DataTables total rows) — the main query JOINs are only needed for display/filter.
	 */
	public function run()
	{
		if ($this->query === null) {
			throw new InvalidConfigException(self::class . '::$query must be an instance of \yii\db\ActiveQuery.');
		}

		$originalQuery = $this->query;
		$filteredQuery = clone $originalQuery;
		$filteredQuery->where = null;

		$draw = $this->getParam('draw');
		$search = $this->getParam('search', ['value' => null, 'regex' => false]);
		$columns = $this->getParam('columns', []);
		$order = $this->getParam('order', []);

		$filteredQuery = $this->applyFilter($filteredQuery, $columns, $search);
		$filteredQuery = $this->applyOrder($filteredQuery, $columns, $order);
		if (!empty($originalQuery->where)) {
			$filteredQuery->andWhere($originalQuery->where);
		}

		$recordsTotal = (int) Member::find()
			->alias('m')
			->andWhere([
				'm.deleted' => isset($this->requestParams['deleted']) ? $this->requestParams['deleted'] : Member::NO,
			])
			->count();

		$filteredQuery
			->offset($this->getParam('start', 0))
			->limit($this->getParam('length', -1));

		$dataProvider = new ActiveDataProvider([
			'query' => $filteredQuery,
			'pagination' => [
				'pageSize' => $this->getParam('length', 10),
			],
		]);

		Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		try {
			$response = [
				'draw' => (int) $draw,
				'recordsTotal' => $recordsTotal,
				'recordsFiltered' => (int) $dataProvider->getTotalCount(),
				'data' => $this->formatData($filteredQuery, $columns),
			];
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}

		return $this->formatResponse($response);
	}

	/**
	 * @inheritdoc
	 */
	public function formatData(ActiveQuery $query, $columns)
	{
		$countriesByIso = Country::findAllCountries();
		$typeLabels = Member::getTypeLabels();
		$statusLabels = Member::getStatusLabels();
		$membershipTypeLabels = Membership::getTypeLabels();

		return ArrayHelper::toArray($query->all(), [
			Member::class => [
				'id',
				'action' => function (Member $model) {
					$actions = [];

					if ($this->requestParams['deleted'] == Member::YES) {
						if (Yii::$app->user->can('restoreMember')) {
							$actions[] = Html::a('<span class="fa fa-undo"></span>', ['restore', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-success',
								'title' => Yii::t('common', 'Restore'),
								'data' => [
									'toggle' => 'tooltip',
									'dt-operation' => 'restore',
									'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
								],
							]);
						}
						if (Yii::$app->user->can('deleteMember')) {
							$actions[] = Html::a('<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
								'class' => 'action-delete btn btn-xs btn-danger',
								'title' => Yii::t('common', 'Delete Permanently'),
								'data' => [
									'toggle' => 'tooltip',
									'dt-operation' => 'delete-permanently',
									'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
								],
							]);
						}
					} else {
						if (Yii::$app->user->can('viewMember')) {
							$actions[] = Html::a('<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-info',
								'title' => Yii::t('common', 'View'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('updateMember')) {
							$actions[] = Html::a('<span class="fa fa-edit"></span>', ['update', 'id' => $model->id], [
								'class' => 'action-update btn btn-xs btn-primary',
								'title' => Yii::t('common', 'Update'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('deleteMember')) {
							$actions[] = Html::a('<span class="fa fa-trash"></span>', ['delete', 'id' => $model->id], [
								'class' => 'action-delete btn btn-xs btn-danger',
								'title' => Yii::t('common', 'Delete'),
								'data' => [
									'toggle' => 'tooltip',
									'dt-operation' => 'delete',
									'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
								],
							]);
						}
					}

					$actions = array_map(function ($actionsChunk) {
						return Html::tag('div', implode('', $actionsChunk));
					}, array_chunk($actions, 3));

					return implode('', $actions);
				},
				'figure' => function (Member $model) {
					if ($model->image) {
						$imgTag = Html::img($model->getImageUrl(), [
							'class' => 'img-responsive',
							'alt' => $model->translation->role,
						]);
						return Html::a($imgTag, $model->getImageUrl(), [
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
				'name' => function (Member $model) {
					return $model->fullName ?: '&mdash;';
				},
				'first_name' => function (Member $model) {
					return $model->first_name ?: '&mdash;';
				},
				'middle_name' => function (Member $model) {
					return $model->middle_name ?: '&mdash;';
				},
				'last_name' => function (Member $model) {
					return $model->last_name ?: '&mdash;';
				},
				'email' => function (Member $model) {
					return $model->email ? Html::a($model->email, 'mailto:' . $model->email) : '&mdash;';
				},
				'phone' => function (Member $model) {
					return $model->phone ? Html::a($model->phone, 'tel:' . $model->phone) : '&mdash;';
				},
				'date_of_birth' => function (Member $model) {
					return $model->date_of_birth ? Yii::$app->formatter->asDate($model->date_of_birth) : '&mdash;';
				},
				'address' => function (Member $model) {
					return $model->address ?: '&mdash;';
				},
				'locality' => function (Member $model) {
					return $model->locality ?: '&mdash;';
				},
				'country' => function (Member $model) use ($countriesByIso) {
					if ($model->country) {
						$country = $countriesByIso[$model->country] ?? null;
						return $country ? $country->translation->name : $model->country;
					}
					return '&mdash;';
				},
				'type' => function (Member $model) use ($typeLabels) {
					$type = $typeLabels[$model->type] ?? null;
					return $type ?: '&mdash;';
				},
				'membership' => function (Member $model) use ($membershipTypeLabels) {
					$membership = $model->activeMembership;
					if (!$membership) {
						return '&mdash;';
					}
					$typeLabel = $membershipTypeLabels[$membership->type] ?? Yii::t('common', 'Membership');
					$badge = Html::tag('span', $typeLabel, ['class' => 'label label-success']);
					$period = '';
					if ($membership->start_at || $membership->end_at) {
						$start = $membership->start_at ? Yii::$app->formatter->asDate($membership->start_at) : '?';
						$end = $membership->end_at ? Yii::$app->formatter->asDate($membership->end_at) : '?';
						$period = Html::tag('div', $start . ' &ndash; ' . $end, ['class' => 'text-muted small']);
					}
					return $badge . $period;
				},
				'role' => function (Member $model) {
					return $model->translation->role ?: '&mdash;';
				},
				'sort_order',
				'created_by' => function (Member $model) {
					return $model->creator ? $model->creator->getFullName() : '&mdash;';
				},
				'created_at' => function (Member $model) {
					return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '&mdash;';
				},
				'updated_at' => function (Member $model) {
					return $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '&mdash;';
				},
				'status' => function (Member $model) use ($statusLabels) {
					$status = $statusLabels[$model->status];
					return Html::tag('span', $status['label'], ['class' => 'label label-block label-' . $status['color']]);
				},
			],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function applyFilter(ActiveQuery $query, $columns, $search)
	{
		/** @var \yii\db\ActiveRecord $modelClass */
		$modelClass = $query->modelClass;
		$schema = $modelClass::getTableSchema()->columns;

		foreach ($columns as $column) {
			if ($column['searchable'] == 'false') {
				continue;
			}
			if (!empty($search['value'])) {
				$value = trim($search['value']);
				$filterOperator = 'orFilterWhere';
			} else {
				$value = trim($column['search']['value']);
				$filterOperator = 'andFilterWhere';
			}

			switch ($column['data']) {
				case 'role':
					$query->$filterOperator(['LIKE', 'mt.role', $value]);
					break;
				case 'created_by':
					$query->$filterOperator([
						'OR',
						['LIKE', 'cr.first_name', $value],
						['LIKE', 'cr.middle_name', $value],
						['LIKE', 'cr.last_name', $value],
					]);
					break;
				case 'date_of_birth':
					$query->$filterOperator(['LIKE', 'm.date_of_birth', DateHelper::formatAsDate($value)]);
					break;
				case 'created_at':
					$query->$filterOperator(['LIKE', 'm.created_at', DateHelper::formatAsDate($value)]);
					break;
				case 'updated_at':
					$query->$filterOperator(['LIKE', 'm.updated_at', DateHelper::formatAsDate($value)]);
					break;
				case 'membership':
					if ($value !== '' && $value !== null) {
						$membershipTable = Membership::tableName();
						$query->andWhere([
							'EXISTS',
							(new \yii\db\Query())
								->from($membershipTable . ' am')
								->where('am.member_id = m.id')
								->andWhere([
									'am.status' => Membership::STATUS_ACTIVE,
									'am.deleted' => Membership::NO,
									'am.type' => (int) $value,
								]),
						]);
					}
					break;
				default:
					if (array_key_exists($column['data'], $schema)) {
						$query->$filterOperator(['LIKE', 'm.' . $column['data'], $value]);
					}
					break;
			}
		}
		return $query;
	}

	/**
	 * @inheritdoc
	 */
	public function applyOrder(ActiveQuery $query, $columns, $order)
	{
		foreach ($order as $key => $item) {
			$column = $columns[$item['column']];
			if (array_key_exists('orderable', $column) && $column['orderable'] === 'false') {
				continue;
			}
			$sort = mb_strtolower($item['dir']) == 'desc' ? SORT_DESC : SORT_ASC;

			switch ($column['data']) {
				case 'role':
					$query->addOrderBy(['mt.role' => $sort]);
					break;
				case 'created_by':
					$query->addOrderBy([
						'cr.first_name' => $sort,
						'cr.middle_name' => $sort,
						'cr.last_name' => $sort,
					]);
					break;
				default:
					$query->addOrderBy(['m.' . $column['data'] => $sort]);
					break;
			}
		}
		return $query;
	}
}

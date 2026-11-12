<?php

namespace backend\modules\setting\models;

use common\helpers\DateHelper;
use common\models\Branch;
use common\models\Country;
use common\widgets\datatable\DataTableAction;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class BranchSearch extends DataTableAction
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->query = Branch::find()
			->alias('b')
			->joinWith([
				'creator cr' => function (ActiveQuery $query) {
					$query->select([
						'cr.id',
						'cr.first_name',
						'cr.middle_name',
						'cr.last_name',
					]);
				},
			])
			->andWhere([
				'b.deleted' => isset($this->requestParams['deleted']) ? $this->requestParams['deleted'] : Branch::NO,
			]);
	}

	/**
	 * @inheritdoc
	 */
	public function formatData(ActiveQuery $query, $columns)
	{
		$countriesByIso = Country::findAllCountries();
		$statusLabels = Branch::getStatusLabels();
		$dash = '&mdash;';

		return ArrayHelper::toArray($query->all(), [
			Branch::class => [
				'id',
				'action' => function (Branch $model) {
					$actions = [];

					if ($this->requestParams['deleted'] == Branch::YES) {
						if (Yii::$app->user->can('restoreBranch')) {
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
						if (Yii::$app->user->can('deleteBranch')) {
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
						if (Yii::$app->user->can('viewBranch')) {
							$actions[] = Html::a('<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-info',
								'title' => Yii::t('common', 'View'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('updateBranch')) {
							$actions[] = Html::a('<span class="fa fa-edit"></span>', ['update', 'id' => $model->id], [
								'class' => 'action-update btn btn-xs btn-primary',
								'title' => Yii::t('common', 'Update'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('deleteBranch')) {
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

					return implode('', $actions);
				},
				'sort_order',
				'name' => function (Branch $model) use ($dash) {
					return $model->name ? Html::encode($model->name) : $dash;
				},
				'contact_name' => function (Branch $model) use ($dash) {
					return $model->contactName ? Html::encode($model->contactName) : $dash;
				},
				'email' => function (Branch $model) use ($dash) {
					return $model->email ? Html::a(Html::encode($model->email), 'mailto:' . $model->email) : $dash;
				},
				'mobile_phone' => function (Branch $model) use ($dash) {
					return $model->mobile_phone ? Html::a(Html::encode($model->mobile_phone), 'tel:' . $model->mobile_phone) : $dash;
				},
				'fixed_phone' => function (Branch $model) use ($dash) {
					return $model->fixed_phone ? Html::a(Html::encode($model->fixed_phone), 'tel:' . $model->fixed_phone) : $dash;
				},
				'address' => function (Branch $model) use ($dash) {
					return $model->address ? Html::encode($model->address) : $dash;
				},
				'locality' => function (Branch $model) use ($dash) {
					return $model->locality ? Html::encode($model->locality) : $dash;
				},
				'county' => function (Branch $model) use ($dash) {
					return $model->county ? Html::encode($model->county) : $dash;
				},
				'country' => function (Branch $model) use ($countriesByIso, $dash) {
					if ($model->country) {
						$country = $countriesByIso[$model->country] ?? null;
						return $country ? $country->translation->name : Html::encode($model->country);
					}
					return $dash;
				},
				'created_by' => function (Branch $model) use ($dash) {
					return $model->creator ? $model->creator->getFullName() : $dash;
				},
				'created_at' => function (Branch $model) use ($dash) {
					return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : $dash;
				},
				'status' => function (Branch $model) use ($statusLabels) {
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
		$schema = Branch::getTableSchema()->columns;

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
				case 'contact_name':
					$query->$filterOperator([
						'OR',
						['LIKE', 'b.first_name', $value],
						['LIKE', 'b.middle_name', $value],
						['LIKE', 'b.last_name', $value],
					]);
					break;
				case 'created_by':
					$query->$filterOperator([
						'OR',
						['LIKE', 'cr.first_name', $value],
						['LIKE', 'cr.middle_name', $value],
						['LIKE', 'cr.last_name', $value],
					]);
					break;
				case 'created_at':
					$query->$filterOperator(['LIKE', 'b.created_at', DateHelper::formatAsDate($value)]);
					break;
				case 'status':
					$query->andFilterWhere(['b.status' => $value]);
					break;
				default:
					if (array_key_exists($column['data'], $schema)) {
						$query->$filterOperator(['LIKE', 'b.' . $column['data'], $value]);
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
		$schema = Branch::getTableSchema()->columns;

		foreach ($order as $item) {
			$column = $columns[$item['column']];
			if (array_key_exists('orderable', $column) && $column['orderable'] === 'false') {
				continue;
			}
			$sort = mb_strtolower($item['dir']) == 'desc' ? SORT_DESC : SORT_ASC;

			switch ($column['data']) {
				case 'contact_name':
					$query->addOrderBy(['b.last_name' => $sort, 'b.first_name' => $sort]);
					break;
				case 'created_by':
					$query->addOrderBy([
						'cr.first_name' => $sort,
						'cr.middle_name' => $sort,
						'cr.last_name' => $sort,
					]);
					break;
				default:
					if (array_key_exists($column['data'], $schema)) {
						$query->addOrderBy(['b.' . $column['data'] => $sort]);
					}
					break;
			}
		}
		return $query;
	}
}

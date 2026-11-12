<?php

namespace backend\modules\nomenclature\models;

use common\helpers\DateHelper;
use common\models\ArticleCategory;
use common\models\ArticleCategoryTranslation;
use common\widgets\datatable\DataTableAction;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ActivityCategorySearch extends DataTableAction
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->query = ArticleCategory::find()
			->alias('c')
			->select([
				'c.id',
				'c.sort_order',
				'c.created_by',
				'c.updated_by',
				'c.created_at',
				'c.updated_at',
				'c.status',
			])
			->joinWith([
				'articleCategoryTranslations ct' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'ct.language_id' => Yii::$app->language,
						'ct.deleted' => ArticleCategoryTranslation::NO,
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
			->andWhere([
                'c.type' => ArticleCategory::TYPE_ACTIVITY,
				'c.deleted' => isset($this->requestParams['deleted']) ? $this->requestParams['deleted'] : ArticleCategory::NO,
			]);
	}

	/**
	 * @inheritdoc
	 */
	public function formatData(ActiveQuery $query, $columns)
	{
		return ArrayHelper::toArray($query->all(), [
			ArticleCategory::class => [
				'id',
				'action' => function (ArticleCategory $model) {
					$actions = [];

					if ($this->requestParams['deleted'] == ArticleCategory::YES) {
						if (Yii::$app->user->can('restoreArticleCategory')) {
							$actions[] = Html::a('<span class="fa fa-undo"></span>', ['/nomenclature-manager/activity-category/restore', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-success',
								'title' => Yii::t('common', 'Restore'),
								'data' => [
									'toggle' => 'tooltip',
									'dt-operation' => 'restore',
									'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
								],
							]);
						}
						if (Yii::$app->user->can('deleteArticleCategory')) {
							$actions[] = Html::a('<span class="fa fa-trash"></span>', ['/nomenclature-manager/activity-category/delete', 'id' => $model->id], [
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
						if (Yii::$app->user->can('viewArticleCategory')) {
							$actions[] = Html::a('<span class="fa fa-eye"></span>', ['/nomenclature-manager/activity-category/view', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-info',
								'title' => Yii::t('common', 'View'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('updateArticleCategory')) {
							$actions[] = Html::a('<span class="fa fa-edit"></span>', ['/nomenclature-manager/activity-category/update', 'id' => $model->id], [
								'class' => 'action-update btn btn-xs btn-primary',
								'title' => Yii::t('common', 'Update'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('deleteArticleCategory')) {
							$actions[] = Html::a('<span class="fa fa-trash"></span>', ['/nomenclature-manager/activity-category/delete', 'id' => $model->id], [
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
				'sort_order',
				'title' => function (ArticleCategory $model) {
                    return $model->getTreeName($glue = ' &rsaquo; ');
				},
				'created_by' => function (ArticleCategory $model) {
					return $model->creator ? $model->creator->getFullName() : '&mdash;';
				},
				'created_at' => function (ArticleCategory $model) {
					return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '&mdash;';
				},
				'updated_at' => function (ArticleCategory $model) {
					return $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '&mdash;';
				},
				'status' => function (ArticleCategory $model) {
					$status = ArticleCategory::getStatusLabels()[$model->status];
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
				case 'title':
					$query->$filterOperator(['LIKE', 'ct.title', $value]);
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
					$query->$filterOperator(['LIKE', 'c.created_at', DateHelper::formatAsDate($value)]);
					break;
				case 'updated_at':
					$query->$filterOperator(['LIKE', 'c.updated_at', DateHelper::formatAsDate($value)]);
					break;
				default:
					if (array_key_exists($column['data'], $schema)) {
						$query->$filterOperator(['LIKE', 'c.' . $column['data'], $value]);
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
        /** @var \yii\db\ActiveRecord $modelClass */
        $modelClass = $query->modelClass;
        $schema = $modelClass::getTableSchema()->columns;

        foreach ($order as $key => $item) {
			$column = $columns[$item['column']];
			if (array_key_exists('orderable', $column) && $column['orderable'] === 'false') {
				continue;
			}
			$sort = mb_strtolower($item['dir']) == 'desc' ? SORT_DESC : SORT_ASC;

			switch ($column['data']) {
				case 'title':
					$query->addOrderBy(['ct.title' => $sort]);
					break;
				default:
                    if (array_key_exists($column['data'], $schema)) {
                        $query->addOrderBy(['c.' . $column['data'] => $sort]);
                    }
					break;
			}
		}
		return $query;
	}
}



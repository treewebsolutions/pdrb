<?php

namespace backend\modules\nomenclature\models;

use common\helpers\DateHelper;
use common\models\Competition;
use common\models\CompetitionTranslation;
use common\models\Ranking;
use common\models\StageTranslation;
use common\models\TeamTranslation;
use common\widgets\datatable\DataTableAction;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RankingSearch extends DataTableAction
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->query = Ranking::find()
			->alias('r')
			->select([
				'r.id',
				'r.competition_id',
				'r.stage_id',
				'r.team_id',
				'r.points',
				'r.matches',
				'r.wins',
				'r.draws',
				'r.loses',
				'r.scored',
				'r.received',
				'r.created_by',
				'r.updated_by',
				'r.created_at',
				'r.updated_at',
				'r.status',
			])
			->joinWith([
				'competition c' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'c.deleted' => Competition::NO,
					]);
				},
				'competition.competitionTranslations ct' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'ct.language_id' => Yii::$app->language,
						'ct.deleted' => CompetitionTranslation::NO,
					]);
				},
				'stage.stageTranslations st' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'st.language_id' => Yii::$app->language,
						'st.deleted' => StageTranslation::NO,
					]);
				},
				'team.teamTranslations tt' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'tt.language_id' => Yii::$app->language,
						'tt.deleted' => TeamTranslation::NO,
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
				'r.deleted' => isset($this->requestParams['deleted']) ? $this->requestParams['deleted'] : Ranking::NO,
			])
			->orderBy([
				'c.start_at' => SORT_ASC,
				'ct.title' => SORT_ASC,
			]);
	}

	/**
	 * @inheritdoc
	 */
	public function formatData(ActiveQuery $query, $columns)
	{
		return ArrayHelper::toArray($query->all(), [
			Ranking::class => [
				'id',
				'action' => function (Ranking $model) {
					$actions = [];

					if ($this->requestParams['deleted'] == Ranking::YES) {
						if (Yii::$app->user->can('restoreRanking')) {
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
						if (Yii::$app->user->can('deleteRanking')) {
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
						if (Yii::$app->user->can('viewRanking')) {
							$actions[] = Html::a('<span class="fa fa-eye"></span>', ['view', 'id' => $model->id], [
								'class' => 'action-view btn btn-xs btn-info',
								'title' => Yii::t('common', 'View'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('updateRanking')) {
							$actions[] = Html::a('<span class="fa fa-edit"></span>', ['update', 'id' => $model->id], [
								'class' => 'action-update btn btn-xs btn-primary',
								'title' => Yii::t('common', 'Update'),
								'data' => [
									'toggle' => 'tooltip',
								],
							]);
						}
						if (Yii::$app->user->can('deleteRanking')) {
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
				'competition' => function (Ranking $model) {
					return $model->competition->translation->title ?: '&mdash;';
				},
				'team' => function (Ranking $model) {
					return $model->team->translation->name ?: '&mdash;';
				},
				'stage' => function (Ranking $model) {
					return $model->stage->translation->title ?: '&mdash;';
				},
				'points' => function (Ranking $model) {
					return $model->points;
				},
				'matches' => function (Ranking $model) {
					return $model->matches;
				},
				'wins' => function (Ranking $model) {
					return $model->wins;
				},
				'draws' => function (Ranking $model) {
					return $model->draws;
				},
				'loses' => function (Ranking $model) {
					return $model->loses;
				},
				'scored' => function (Ranking $model) {
					return $model->scored;
				},
				'received' => function (Ranking $model) {
					return $model->received;
				},
				'created_by' => function (Ranking $model) {
					return $model->creator ? $model->creator->getFullName() : '&mdash;';
				},
				'created_at' => function (Ranking $model) {
					return $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '&mdash;';
				},
				'updated_at' => function (Ranking $model) {
					return $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '&mdash;';
				},
				'status' => function (Ranking $model) {
					$status = Ranking::getStatusLabels()[$model->status];
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
				case 'created_by':
					$query->$filterOperator([
						'OR',
						['LIKE', 'cr.first_name', $value],
						['LIKE', 'cr.middle_name', $value],
						['LIKE', 'cr.last_name', $value],
					]);
					break;
				case 'created_at':
					$query->$filterOperator(['LIKE', 'r.created_at', DateHelper::formatAsDate($value)]);
					break;
				case 'updated_at':
					$query->$filterOperator(['LIKE', 'r.updated_at', DateHelper::formatAsDate($value)]);
					break;
				default:
					if (array_key_exists($column['data'], $schema)) {
						$query->$filterOperator(['LIKE', 'r.' . $column['data'], $value]);
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
				case 'competition':
					$query->addOrderBy(['ct.title' => $sort]);
					break;
				case 'stage':
					$query->addOrderBy(['st.title' => $sort]);
					break;
				case 'team':
					$query->addOrderBy(['tt.name' => $sort]);
					break;
				case 'created_by':
					$query->addOrderBy([
						'cr.first_name' => $sort,
						'cr.middle_name' => $sort,
						'cr.last_name' => $sort,
					]);
					break;
				default:
					$query->addOrderBy(['r.' . $column['data'] => $sort]);
					break;
			}
		}
		return $query;
	}
}

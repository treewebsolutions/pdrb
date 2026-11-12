<?php

namespace backend\modules\setting\controllers;

use backend\controllers\MainController;
use backend\modules\setting\models\BranchForm;
use backend\modules\setting\models\BranchSearch;
use common\models\Branch;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class BranchController extends MainController
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index', 'view', 'dt-branches'],
						'roles' => ['viewBranch'],
					],
					[
						'allow' => true,
						'actions' => ['create'],
						'roles' => ['createBranch'],
					],
					[
						'allow' => true,
						'actions' => ['update'],
						'roles' => ['updateBranch'],
					],
					[
						'allow' => true,
						'actions' => ['delete'],
						'roles' => ['deleteBranch'],
						'verbs' => ['POST'],
					],
					[
						'allow' => true,
						'actions' => ['restore'],
						'roles' => ['restoreBranch'],
						'verbs' => ['POST'],
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'dt-branches' => BranchSearch::class,
		];
	}

	/**
	 * Lists all Branch models.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		if (Yii::$app->request->get('deleted') == Branch::YES) {
			if (!Yii::$app->settings->get('enableSoftDelete') || !Yii::$app->user->can('restoreBranch')) {
				return $this->redirect(['index']);
			}
		}
		return $this->render('index');
	}

	/**
	 * Displays a single Branch model.
	 *
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * Creates a new Branch model.
	 *
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new BranchForm();

		Yii::$app->eventLog->beginRecord($model);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			$this->invalidateCache();
			Yii::$app->eventLog->endRecord();
			Yii::$app->session->setFlash('success', Yii::t('common', 'Record has been created.'));

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Branch model.
	 *
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id, BranchForm::class);

		Yii::$app->eventLog->beginRecord($model);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			$this->invalidateCache();
			Yii::$app->eventLog->endRecord();
			Yii::$app->session->setFlash('success', Yii::t('common', 'Record has been updated.'));

			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes existing Branch models (soft or permanent).
	 *
	 * @param null|int $id
	 * @return mixed
	 * @throws NotFoundHttpException if no model was found
	 */
	public function actionDelete($id = null)
	{
		$bodyParams = Yii::$app->request->post();
		$isPermanent = !Yii::$app->settings->get('enableSoftDelete')
			|| (($bodyParams['dt_operation'] ?? null) == 'delete-permanently' || ($bodyParams['dt_bulk_operation'] ?? null) == 'delete-permanently');
		if ($id === null) {
			$id = Yii::$app->request->post('selection');
		}
		$models = $this->findModel($id, null, true);
		$response = [
			'success' => true,
			'message' => [
				'title' => Yii::t('common', 'The delete operation was successful.'),
				'body' => [],
			],
		];
		$dbTransaction = Yii::$app->db->beginTransaction();
		try {
			/** @var Branch $model */
			foreach ($models->each() as $model) {
				Yii::$app->eventLog
					->setData([
						'operation' => $isPermanent ? (Yii::$app->eventLog)::ACTION_DELETE : (Yii::$app->eventLog)::ACTION_SOFT_DELETE,
					])
					->beginRecord($model);
				if (!$model->delete($isPermanent)) {
					throw new \Exception();
				}
				Yii::$app->eventLog->endRecord();
			}
			$this->invalidateCache();
			$dbTransaction->commit();
		} catch (\Throwable $e) {
			$dbTransaction->rollBack();
			$response['success'] = false;
			$response['message']['title'] = Yii::t('common', 'The delete operation was unsuccessful.');
		}

		if (Yii::$app->request->isAjax) {
			return $this->asJson($response);
		}
		Yii::$app->session->setFlash($response['success'] ? 'success' : 'error', [$response['message']]);

		return $this->redirect(['index']);
	}

	/**
	 * Restores Branch models that are marked as deleted.
	 *
	 * @param null|int $id
	 * @return mixed
	 * @throws NotFoundHttpException if no model was found
	 */
	public function actionRestore($id = null)
	{
		if ($id === null) {
			$id = Yii::$app->request->post('selection');
		}
		$models = $this->findModel($id, null, true);
		$response = [
			'success' => true,
			'message' => [
				'title' => Yii::t('common', 'The restore operation was successful.'),
				'body' => [],
			],
		];
		$dbTransaction = Yii::$app->db->beginTransaction();
		try {
			/** @var Branch $model */
			foreach ($models->each() as $model) {
				Yii::$app->eventLog->beginRecord($model);
				if (!$model->restore()) {
					throw new \Exception();
				}
				Yii::$app->eventLog->endRecord();
			}
			$this->invalidateCache();
			$dbTransaction->commit();
		} catch (\Throwable $e) {
			$dbTransaction->rollBack();
			$response['success'] = false;
			$response['message']['title'] = Yii::t('common', 'The restore operation was unsuccessful.');
		}

		if (Yii::$app->request->isAjax) {
			return $this->asJson($response);
		}
		Yii::$app->session->setFlash($response['success'] ? 'success' : 'error', [$response['message']]);

		return $this->redirect(['index']);
	}

	/**
	 * Invalidates the cached branch list used by the frontend.
	 */
	protected function invalidateCache()
	{
		Yii::$app->trigger('invalidate.cache', new \tws\caching\CacheEvent(['key' => 'findAllBranches']));
	}

	/**
	 * Finds the Branch model(s) based on its primary key value.
	 *
	 * @param int|array $id
	 * @param string|null $modelName
	 * @param bool $asActiveQuery
	 * @return \yii\db\ActiveQuery|Branch|BranchForm
	 * @throws NotFoundHttpException if no model was found
	 */
	protected function findModel($id, $modelName = null, $asActiveQuery = false)
	{
		$modelName = $modelName && class_exists($modelName) ? $modelName : Branch::class;
		$query = $modelName::find()->andWhere([
			'id' => $id,
		]);

		if ($asActiveQuery) {
			if (!$query->count()) {
				throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
			}
			return $query;
		}

		$query->andWhere(['deleted' => Branch::NO]);
		if (($model = $query->one()) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}
}

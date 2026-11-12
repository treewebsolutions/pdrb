<?php

namespace backend\modules\nomenclature\controllers;

use backend\controllers\MainController;
use backend\modules\nomenclature\models\MemberForm;
use backend\modules\nomenclature\models\MemberSearch;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use common\helpers\Inflector;
use common\helpers\UploadHelper;
use common\models\Member;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

class MemberController extends MainController
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
					'actions' => ['index', 'view', 'export', 'dt-members', 'import'],
					'roles' => ['viewMember'],
				],
					[
						'allow' => true,
						'actions' => ['create'],
						'roles' => ['createMember'],
					],
					[
						'allow' => true,
						'actions' => ['update', 'delete-file'],
						'roles' => ['updateMember'],
					],
					[
						'allow' => true,
						'actions' => ['delete'],
						'roles' => ['deleteMember'],
						'verbs' => ['POST'],
					],
					[
						'allow' => true,
						'actions' => ['restore'],
						'roles' => ['restoreMember'],
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
			'dt-members' => MemberSearch::class,
		];
	}

	/**
	 * Lists all Member models.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		if (Yii::$app->request->get('deleted') == Member::YES) {
			if (!Yii::$app->settings->get('enableSoftDelete') || !Yii::$app->user->can('restoreMember')) {
				return $this->redirect(['index']);
			}
		}
		return $this->render('index');
	}

	/**
	 * Displays a single Member model.
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
	 * Imports members from spreadsheet.
	 *
	 * @return mixed
	 */
	public function actionImport()
	{
		return $this->render('import');
	}

	/**
	 * Creates a new Member model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new MemberForm();
		$result = true;

		Yii::$app->eventLog->beginRecord($model);
		if ($model->load(Yii::$app->request->post()) && ($result = $model->save())) {
			Yii::$app->trigger('invalidate.cache', new \tws\caching\CacheEvent(['key' => 'findAllMembers']));
			Yii::$app->eventLog->endRecord();

			$message = Yii::t('common', 'Record has been created.');
			if (Yii::$app->request->isAjax) {
				return $this->asJson([
					'success' => true,
					'message' => $message,
				]);
			}
			Yii::$app->session->setFlash('success', $message);

			return $this->redirect(['view', 'id' => $model->id]);
		}

		if (Yii::$app->request->isAjax) {
			return $this->asJson([
				'success' => (bool) $result,
				'data' => $this->renderAjax('create', [
					'model' => $model,
				]),
			]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Member model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id, MemberForm::class);
		$result = true;

		Yii::$app->eventLog->beginRecord($model);
		if ($model->load(Yii::$app->request->post()) && ($result = $model->save())) {
			Yii::$app->trigger('invalidate.cache', new \tws\caching\CacheEvent(['key' => 'findAllMembers']));
			Yii::$app->eventLog->endRecord();

			$message = Yii::t('common', 'Record has been updated.');
			if (Yii::$app->request->isAjax) {
				return $this->asJson([
					'success' => true,
					'message' => $message,
				]);
			}
			Yii::$app->session->setFlash('success', $message);

			return $this->redirect(['view', 'id' => $model->id]);
		}

		if (Yii::$app->request->isAjax) {
			return $this->asJson([
				'success' => (bool) $result,
				'data' => $this->renderAjax('update', [
					'model' => $model,
				]),
			]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes existing Member models.
	 * If deletion is successful, JSON is returned or the browser will be redirected to the 'index' page.
	 *
	 * @param null|int $id
	 * @return mixed
	 * @throws \Throwable
	 * @throws NotFoundHttpException if no model was found
	 */
	public function actionDelete($id = null)
	{
		$bodyParams = Yii::$app->request->post();
		$isPermanent = !Yii::$app->settings->get('enableSoftDelete') || ($bodyParams['dt_operation'] == 'delete-permanently' || $bodyParams['dt_bulk_operation'] == 'delete-permanently');
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
			$deletedModels = [];
			/** @var Member $model */
			foreach ($models->each() as $model) {
				Yii::$app->eventLog
					->setData([
						'operation' => $isPermanent ? (Yii::$app->eventLog)::ACTION_DELETE : (Yii::$app->eventLog)::ACTION_SOFT_DELETE,
					])
					->beginRecord($model);
				if ($model->delete($isPermanent)) {
					$deletedModels[] = $model->id;
					Yii::$app->eventLog->endRecord();
				} else {
					throw new \Exception();
				}
			}
			if ($isPermanent) {
				foreach ($deletedModels as $deletedModel) {
					FileHelper::removeDirectory(Yii::getAlias("@uploads/{$this->id}/{$deletedModel}"));
				}
			}
			Yii::$app->trigger('invalidate.cache', new \tws\caching\CacheEvent(['key' => 'findAllMembers']));
			$dbTransaction->commit();
		} catch (\Exception $e) {
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
	 * Restores Member models that are marked as deleted.
	 * If restoration is successful, JSON is returned or the browser will be redirected to the 'index' page.
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
			/** @var Member $model */
			foreach ($models->each() as $model) {
				Yii::$app->eventLog->beginRecord($model);
				if ($model->restore()) {
					Yii::$app->eventLog->endRecord();
				} else {
					throw new \Exception();
				}
			}
			Yii::$app->trigger('invalidate.cache', new \tws\caching\CacheEvent(['key' => 'findAllMembers']));
			$dbTransaction->commit();
		} catch (\Exception $e) {
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
	 * Delete an existing file.
	 *
	 * @param $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDeleteFile($id)
	{
		if (!Yii::$app->request->isAjax) {
			throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
		}
		$model = $this->findModel($id);
		$attribute = Yii::$app->request->post('attribute', 'image');
		$filePath = Yii::getAlias("@uploads/{$this->id}/{$id}/{$model->$attribute}");
		$result = true;

		if (file_exists($filePath) && ($result = FileHelper::unlink($filePath))) {
			$model->$attribute = null;
			$model->save(false);
		}
		return $this->asJson([
			'success' => (bool) $result,
			'message' => $result ?
				Yii::t('common', 'Record has been deleted.') :
				Yii::t('common', 'Cannot delete the record.'),
		]);
	}

	/**
	 * Exports data to a specific file format.
	 *
	 * @return \yii\web\Response
	 */
	public function actionExport()
	{
		try {
			$bodyParams = Yii::$app->request->post();
			$dataTable = $bodyParams['dataTable'];
			$result = null;

			// Create a new DataTableAction model
			$model = $this->getDataTableActionModel($bodyParams['model']);
			// Set the externalFilters
			if (is_array($dataTable['external_filters']) && !empty($dataTable['external_filters'])) {
				$model->externalFilters = ArrayHelper::map($dataTable['external_filters'], 'name', 'value');
			}

			$model->applyFilter($model->query, $dataTable['columns'], $dataTable['search']);
			$model->applyOrder($model->query, $dataTable['columns'], $dataTable['order']);

			// Get data, then filter the columns to be exported
			if (method_exists($model, 'getExportData')) {
				$records = $model->getExportData($model->query, []);
			} else {
				$records = $model->formatData($model->query, []);
			}
			$columns = array_filter($dataTable['columns'], function ($item) {
				// Exclude action and figure columns
				if (in_array($item['name'], ['action', 'figure'])) {
					return false;
				}
				// Exclude 'name' column from export (we use separate first_name, middle_name, last_name instead)
				if ($item['name'] === 'name') {
					return false;
				}
				// Include columns that are visible OR are the separate name columns
				if (!empty($item['name'])) {
					if ($item['visible'] == 'true') {
						return true;
					}
					// Include first_name, middle_name, last_name even if invisible
					if (in_array($item['name'], ['first_name', 'middle_name', 'last_name'])) {
						return true;
					}
				}
				return false;
			});
			$result = $this->exportAsXlsx($bodyParams, $columns, $records);
			if (empty($result)) {
				throw new \Exception(Yii::t('common', 'Cannot export the requested data.'));
			}

			return $this->asJson([
				'success' => true,
				'returnUrl' => $result,
			]);
		} catch (\Exception $e) {
			return $this->asJson([
				'success' => false,
				'message' => $e->getMessage(),
			]);
		}
	}

	/**
	 * Export as XLSX.
	 *
	 * @param array $bodyParams
	 * @param array $columns
	 * @param array $records
	 * @return string|bool
	 * @throws \Box\Spout\Common\Exception\IOException
	 * @throws \Box\Spout\Common\Exception\InvalidArgumentException
	 * @throws \Box\Spout\Common\Exception\SpoutException
	 * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
	 * @throws \Box\Spout\Writer\Exception\WriterAlreadyOpenedException
	 * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
	 * @throws \yii\base\ErrorException
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function exportAsXlsx($bodyParams, $columns, $records)
	{
		/** @var \Box\Spout\Writer\XLSX\Writer $writer */
		$writer = WriterFactory::create(Type::XLSX);

		if (isset($bodyParams['config']['shouldCreateNewSheetsAutomatically'])) {
			$writer->setShouldCreateNewSheetsAutomatically($bodyParams['config']['shouldCreateNewSheetsAutomatically']);
		}
		if (isset($bodyParams['config']['shouldUseInlineStrings'])) {
			$writer->setShouldUseInlineStrings($bodyParams['config']['shouldUseInlineStrings']);
		}

		// Ensure the file path
		$filePath = 'export/' . Yii::$app->user->id;
		$name = [$bodyParams['title']];
		$name[] = Yii::$app->name;
		$name[] = date('d.m.Y');
		$fileName = Inflector::slug(implode('-', $name)) . ".xlsx";
		FileHelper::removeDirectory(Yii::getAlias("@uploads/{$filePath}"));
		UploadHelper::ensureDirectoryTree($filePath);

		// Write data to the file
		$writer->openToFile(Yii::getAlias("@uploads/{$filePath}/{$fileName}"));
		$writer->addRowWithStyle(ArrayHelper::getColumn($columns, 'title'), (new StyleBuilder())->setFontBold()->build());

		foreach ($records as $record) {
			$row = [];
			foreach ($columns as $column) {
				$value = strip_tags(html_entity_decode($record[$column['name']]));
				// Convert &mdash; to empty string for all columns in export
				if ($value === '—') {
					$value = '';
				}
				if (is_numeric($value)) {
					if (in_array($column['name'], ['phone'])) {
						$value = $value . ' ';
					} else {
						$value = (double)$value;
					}
				}
				$row[] = $value;
			}
			$writer->addRow($row);
		}

		$writer->close();

		return UploadHelper::getFileUrl("{$filePath}/{$fileName}");
	}

	/**
	 * Creates a new instance of a DataTableAction.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param \common\widgets\datatable\DataTableAction $className
	 * @return \common\widgets\datatable\DataTableAction the loaded model
	 * @throws \yii\web\NotFoundHttpException if the model cannot be found
	 */
	protected function getDataTableActionModel($className)
	{
		if (class_exists($className)) {
			return new $className('dt-action', $this->id);
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}

	/**
	 * Finds the Member model(s) based on its primary key value.
	 * A 404 HTTP exception will be thrown if no record was found.
	 *
	 * @param int|array $id
	 * @param \yii\db\ActiveRecord|null $modelName
	 * @param bool $asActiveQuery
	 * @return \yii\db\ActiveQuery|\yii\db\ActiveRecord|Member|MemberForm
	 * @throws NotFoundHttpException if no model was found
	 */
	protected function findModel($id, $modelName = null, $asActiveQuery = false)
	{
		$modelName = class_exists($modelName) ? $modelName : Member::class;
		$query = $modelName::find()->andWhere([
			'id' => $id,
		]);

		if ($asActiveQuery) {
			if (!$query->count()) {
				throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
			}
			return $query;
		}

		$query->andWhere(['deleted' => Member::NO]);
		if (($model = $query->one()) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}
}

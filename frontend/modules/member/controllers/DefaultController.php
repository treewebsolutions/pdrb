<?php

namespace frontend\modules\member\controllers;

use common\models\Member;
use common\models\MemberTranslation;
use frontend\controllers\MainController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class DefaultController extends MainController
{
	/**
	 * Displays index view with all members.
	 *
	 * @param int|null $type
	 * @return mixed
	 */
	public function actionIndex($type = null)
	{
		$view = $this->getView();
		$canonicalUrl = Url::canonical();
		$modelTranslation = null;

		if ($page = \common\models\Page::findPageByRoute(["/{$this->module->id}/{$this->id}/index"])) {
			$modelTranslation = $page->getTranslation();
			$view->title = $modelTranslation->title;
		}

		// Standard meta tags
		if ($modelTranslation) {
			$view->registerMetaTag(['name' => 'description', 'content' => $modelTranslation->description], 'description');
			$view->registerMetaTag(['name' => 'keywords', 'content' => $modelTranslation->keywords], 'keywords');
		}
		$view->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');

		// Basic metadata for open graph
		$view->registerMetaTag(['property' => 'og:type', 'content' => 'website'], 'og:type');
		$view->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
		if ($modelTranslation) {
			$view->registerMetaTag(['property' => 'og:title', 'content' => $modelTranslation->title], 'og:title');
			$view->registerMetaTag(['property' => 'og:description', 'content' => $modelTranslation->description], 'og:description');
		}

		$query = Member::find()
			->alias('m')
			->joinWith([
				'memberTranslations mt' => function (ActiveQuery $query) {
					$query->andOnCondition(['mt.language_id' => Yii::$app->language]);
				},
			])
			->where([
				'm.status' => Member::STATUS_ACTIVE,
				'm.deleted' => Member::NO,
			])
			->orderBy(['m.sort_order' => SORT_ASC])
			->addOrderBy(['m.type' => SORT_ASC]);

		if (!empty($type)) {
			$query->andWhere(['m.type' => $type]);
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'defaultPageSize' => 24,
			],
		]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Displays a single Member model.
	 *
	 * @param string $slug
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionView($slug)
	{
		$model = $this->findModel($slug);

		$modelTranslation = $model->getTranslation();
		$view = $this->getView();
		$canonicalUrl = Url::canonical();

		// Standard meta tags
		$view->title = $model->fullName;
		$view->registerMetaTag(['name' => 'description', 'content' => $modelTranslation->description], 'description');
		$view->registerMetaTag(['name' => 'keywords', 'content' => $modelTranslation->keywords], 'keywords');
		$view->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');

		// Basic metadata for open graph
		$view->registerMetaTag(['property' => 'og:type', 'content' => 'profile'], 'og:type');
		$view->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
		$view->registerMetaTag(['property' => 'og:title', 'content' => $model->fullName], 'og:title');
		$view->registerMetaTag(['property' => 'og:description', 'content' => $modelTranslation->description], 'og:description');
		if ($ogImage = $model->getImageUrl(true)) {
			$view->registerMetaTag(['property' => 'og:image', 'content' => $ogImage], 'og:image');
		}

		return $this->render('view', [
			'model' => $model,
			'modelTranslation' => $model->getTranslation(),
		]);
	}

	/**
	 * Finds the Member model based on its slug value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param string $slug
	 * @return array|\yii\db\ActiveRecord|Member
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($slug)
	{
		$model = Member::find()
			->alias('m')
			->joinWith([
				'memberTranslations mt' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'mt.language_id' => Yii::$app->language,
						'mt.deleted' => MemberTranslation::NO,
					]);
				},
			])
			->andWhere([
				'mt.slug' => $slug,
				'm.status' => Member::STATUS_ACTIVE,
				'm.deleted' => Member::NO,
				'mt.language_id' => Yii::$app->language,
			])
			->limit(1);

		if (($model = $model->one()) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}
}


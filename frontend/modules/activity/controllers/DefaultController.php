<?php

namespace frontend\modules\activity\controllers;

use common\models\Article;
use common\models\ArticleCategory;
use common\models\ArticleTranslation;
use frontend\controllers\MainController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use common\helpers\Inflector;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class DefaultController extends MainController
{
	/**
	 * Displays index view.
	 *
	 * @param null|string $category
	 * @param null|string $tag
	 * @param null|int $year
	 * @return mixed
	 */
	public function actionIndex($category = null, $tag = null, $year = null)
	{
		$view = $this->getView();
		$canonicalUrl = Url::canonical();
		$model = null;
		$modelTranslation = null;

		if (!empty($category) && ($model = ArticleCategory::findArticleCategoryBySlug($category, ArticleCategory::TYPE_ACTIVITY))) {
			$modelTranslation = $model->getTranslation();
			$view->title = Yii::t('frontend', $modelTranslation->title);
		} else {
			if ($page = \common\models\Page::findPageByRoute(["/{$this->module->id}/{$this->id}/index"])) {
				$modelTranslation = $page->getTranslation();
				$view->title = $modelTranslation->title;
			}
			if (!empty($tag)) {
				$view->title = Yii::t('frontend', 'Activities tagged with: {0}', Inflector::humanize($tag));
			} elseif (!empty($year)) {
				$view->title = Yii::t('frontend', 'Activities from year {0}', $year);
			}
		}

		// Standard meta tags
		$view->registerMetaTag(['name' => 'description', 'content' => $modelTranslation->description], 'description');
		$view->registerMetaTag(['name' => 'keywords', 'content' => $modelTranslation->keywords], 'keywords');
		$view->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');

		// Basic metadata for open graph
		$view->registerMetaTag(['property' => 'og:type', 'content' => 'article'], 'og:type');
		$view->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
		$view->registerMetaTag(['property' => 'og:title', 'content' => $modelTranslation->title], 'og:title');
		$view->registerMetaTag(['property' => 'og:description', 'content' => $modelTranslation->description], 'og:description');

		$dataProvider = new ActiveDataProvider([
			'query' => Article::provideArticles($model->id, $tag, $year, Article::TYPE_ACTIVITY),
			'pagination' => [
				'defaultPageSize' => 12,
			],
		]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Displays a single Article model.
	 *
	 * @param string $slug
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionView($slug)
	{
		$model = $this->findModel($slug);
		$model->updateAttributes(['views' => ++$model->views]);

		$modelTranslation = $model->getTranslation();
		$view = $this->getView();
		$canonicalUrl = Url::canonical();

		// Standard meta tags
		$view->title = $modelTranslation->title;
		$view->registerMetaTag(['name' => 'description', 'content' => $modelTranslation->description], 'description');
		$view->registerMetaTag(['name' => 'keywords', 'content' => $modelTranslation->keywords], 'keywords');
		$view->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');

		// Basic metadata for open graph
		$view->registerMetaTag(['property' => 'og:type', 'content' => 'article'], 'og:type');
		$view->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
		$view->registerMetaTag(['property' => 'og:title', 'content' => $modelTranslation->title], 'og:title');
		$view->registerMetaTag(['property' => 'og:description', 'content' => $modelTranslation->description], 'og:description');
		if ($ogLogo = $model->getImageUrl(true)) {
			$view->registerMetaTag(['property' => 'og:image', 'content' => $ogLogo], 'og:image');
		}


		return $this->render('view', [
			'model' => $model,
			'modelTranslation' => $model->getTranslation(),
		]);
	}

	/**
	 * Finds the Article model based on its slug value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param string $slug
	 * @return array|\yii\db\ActiveRecord|Article
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($slug)
	{
		$model = Article::find()
			->alias('a')
			->joinWith([
				'articleTranslations at' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'at.language_id' => Yii::$app->language,
						'at.deleted' => ArticleTranslation::NO,
					]);
				},
			])
			->andWhere([
				'at.slug' => $slug,
				'a.status' => Article::STATUS_ACTIVE,
				'a.deleted' => Article::NO,
				'a.type' => Article::TYPE_ACTIVITY,
                'at.language_id' => Yii::$app->language,
			])
			->limit(1);

		if (($model = $model->one()) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}
}



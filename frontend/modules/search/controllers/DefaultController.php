<?php

namespace frontend\modules\search\controllers;

use common\models\Article;
use common\models\ArticleTranslation;
use common\models\Language;
use common\models\PageTranslation;
use frontend\controllers\MainController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class DefaultController extends MainController
{
	/**
	 * Displays search results for articles.
	 *
	 * @param null|string $q Search query
	 * @return mixed
	 */
	public function actionIndex($q = null)
	{
		$view = $this->getView();
		$canonicalUrl = Url::canonical();
		$title = Yii::t('frontend', 'Search Results');
		$keywords = '';
		$description = '';

		// Search for articles
		if (!empty($q)) {
			$title = Yii::t('frontend', 'Search results for: {0}', $q);
			$keywords = $q;
			$description = Yii::t('frontend', 'Search results for: {0}', $q);

			$query = Article::find()
				->alias('a')
				->joinWith([
					'articleTranslations at' => function (ActiveQuery $query) {
						$query->andOnCondition([
							'at.language_id' => Yii::$app->language,
							'at.deleted' => ArticleTranslation::NO,
						]);
					},
				])
				->where([
					'a.type' => [Article::TYPE_BLOG, Article::TYPE_ACTIVITY],
					'a.status' => Article::STATUS_ACTIVE,
					'a.deleted' => Article::NO,
				])
				->andFilterWhere([
					'OR',
					['LIKE', 'at.title', $q],
					['LIKE', 'at.keywords', $q],
					['LIKE', 'at.description', $q],
					['LIKE', 'at.content', $q],
				]);

			$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => [
					'pageSize' => 24,
				],
				'sort' => [
					'defaultOrder' => [
						'created_at' => SORT_DESC,
					],
				],
			]);
		} else {
			$title = Yii::t('frontend', 'Search');
			$description = Yii::t('frontend', 'Search for articles');

			$dataProvider = new ActiveDataProvider([
				'query' => Article::find()->where(['id' => 0]), // Empty results
				'pagination' => false,
			]);
		}

		$view->title = $title;

		// Standard meta tags
		$view->registerMetaTag(['name' => 'description', 'content' => $description], 'description');
		$view->registerMetaTag(['name' => 'keywords', 'content' => $keywords], 'keywords');
		$view->registerLinkTag(['rel' => 'canonical', 'href' => $canonicalUrl], 'canonical');

		// Basic metadata for open graph
		$view->registerMetaTag(['property' => 'og:type', 'content' => 'website'], 'og:type');
		$view->registerMetaTag(['property' => 'og:url', 'content' => $canonicalUrl], 'og:url');
		$view->registerMetaTag(['property' => 'og:title', 'content' => $title], 'og:title');
		$view->registerMetaTag(['property' => 'og:description', 'content' => $description], 'og:description');

		return $this->render('index', [
			'dataProvider' => $dataProvider,
			'searchQuery' => $q,
		]);
	}
}

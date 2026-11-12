<?php

namespace backend\controllers;

use backend\models\SearchForm;
use common\models\Country;
use common\models\County;
use kartik\depdrop\DepDropAction;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class SiteController extends MainController
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
						'actions' => ['login'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'logout' => ['POST'],
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
			'error' => [
				'class' => 'yii\web\ErrorAction',
				'layout' => 'blank',
			],
			'search-countries' => [
				'class' => DepDropAction::class,
				'allowEmpty' => true,
				'outputCallback' => function ($selection) {
					return Country::queryCountriesByCounty($selection);
				},
				'selectedCallback' => function ($selection) {
					if (count(Country::queryCountriesByCounty($selection)) == 1) {
						return Country::queryCountriesByCounty($selection)[0];
					} else {
						foreach (Country::queryCountriesByCounty($selection) as $country) {
							$counties = County::find()
								->andWhere([
									'status' => County::STATUS_ACTIVE,
									'deleted' => County::NO,
								])
								->andWhere([
									'LIKE', 'name', $selection
								])
								->orderBy(['name' => SORT_ASC])
								->all();
							if (count($counties) > 0 && strlen($selection) > 1 && $country['id'] == Yii::$app->settings->get('defaultCountry')) {
								return $country;
							}
						}
					}
					return [];
				},
			],
		];
	}

	/**
	 * Displays homepage.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		return $this->render('index');
	}

	/**
	 * Displays login page.
	 *
	 * @return mixed
	 */
	public function actionLogin()
	{
		/** @var \yii\web\UrlManager $frontendAppUrlManager */
		$frontendAppUrlManager = Yii::$app->instance->get('@frontend')->getUrlManager();
		return $this->redirect($frontendAppUrlManager->createAbsoluteUrl(['/site/login']), 301);
	}

	/**
	 * Logs out the current user.
	 *
	 * @return mixed
	 */
	public function actionLogout()
	{
		/** @var \yii\web\UrlManager $frontendAppUrlManager */
		$frontendAppUrlManager = Yii::$app->instance->get('@frontend')->getUrlManager();
		return $this->redirect($frontendAppUrlManager->createAbsoluteUrl(['/site/logout']), 301);
	}

	/**
	 * Searches globally in the site.
	 *
	 * @return mixed
	 */
	public function actionSearch()
	{
		$searchModel = new SearchForm();
		$searchModel->load(Yii::$app->request->get());

		$dataProvider = $searchModel->search();

		return $this->render('search', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}
}

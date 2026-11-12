<?php

namespace frontend\modules\sitemap\controllers;

use common\models\Article;
use common\models\ArticleCategory;
use common\models\Page;
use common\models\PageTranslation;
use frontend\controllers\MainController;
use tws\widgets\sitemap\Sitemap;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends MainController
{

	private static function toXml($array)
	{
		$xml = '';
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_numeric($key)) {
					$key = "element";
				}
				if (is_array($value)) {
					$xml .= "<$key>" . static::toXml($value) . "</$key>";
				} elseif (strlen(trim($value)) === 0) {
					$xml .= "<$key/>";
				} else {
					$xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
				}
			}
		}
		$xml = str_replace(['<element>', '</element>'], ['', ''], $xml);
		return $xml;
	}


	/**
	 * Displays index view.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_RAW;

        $headers = $response->getHeaders();
        $headers->set('Content-Type', 'application/xml; charset=utf-8');

        $dataProvider[0]['sitemap'] = [
			'loc' => implode('', array_filter([
				\tws\helpers\Url::base(true),
				(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
				'/sitemap0',
			])),
		];

		$xmlData = static::toXml($dataProvider);
		$xml = implode('', [
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
			$xmlData,
			'</sitemapindex>'
		]);
		return $xml;
	}

	/**
	 * Displays a single Article model.
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionView()
    {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_RAW;

        $headers = $response->getHeaders();
        $headers->set('Content-Type', 'application/xml; charset=utf-8');

        if (Yii::$app->request->get('index') == 0) {
            $pagesDataProvider = new ActiveDataProvider([
                'query' => Page::find()
                    ->andWhere([
                        'OR',
                        ['IS', 'module', null],
                        ['!=', 'module', 'account'],
                    ])
                    ->andWhere(['NOT IN', 'action', ['subscribe', 'unsuscribe']])
                    ->andWhere([
                        'status' => Page::STATUS_ACTIVE,
                        'deleted' => Page::NO,
                    ]),
                'pagination' => [
                    'pageSize' => 0,
                ],
            ]);
            $articlesPage = Page::findPageByRoute(['/article/default/index']);
            $articlesPageTranslations = [];
            if ($articlesPage) {
                $articlesPageTranslations = $articlesPage->getPageTranslations()
                    ->where([
                        'language_id' => Yii::$app->language
                    ])
                    ->indexBy('language_id')
                    ->all();
            }
            
            // Check if article tables exist before creating data providers
            $articleTableExists = Yii::$app->db->getTableSchema(Article::tableName(), true) !== null;
            $articleCategoryTableExists = Yii::$app->db->getTableSchema(ArticleCategory::tableName(), true) !== null;
            
            $articleCategoriesDataProvider = null;
            if ($articleCategoryTableExists) {
                $articleCategoriesDataProvider = new ActiveDataProvider([
                    'query' => ArticleCategory::find()
                        ->alias('c')
                        ->select([
                            'c.*',
                        ])
                        ->joinWith([
                            'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                                return $query->andOnCondition([
                                    'ct.language_id' => Yii::$app->language,
                                    'ct.deleted' => PageTranslation::NO,
                                ]);
                            },
                        ])
                        ->where([
                            'c.status' => ArticleCategory::STATUS_ACTIVE,
                            'c.deleted' => ArticleCategory::NO,
                            'c.type' => ArticleCategory::TYPE_BLOG,
                            'ct.language_id' => Yii::$app->language,
                        ]),
                    'pagination' => [
                        'pageSize' => 0,
                    ],
                ]);
            }
            
            $articlesDataProvider = null;
            if ($articleTableExists) {
                $articlesDataProvider = new ActiveDataProvider([
                    'query' => Article::find()
                        ->alias('a')
                        ->select([
                            'a.*',
                        ])
                        ->joinWith([
                            'articleTranslations at' => function (ActiveQuery $query) {
                                return $query->andOnCondition([
                                    'at.language_id' => Yii::$app->language,
                                    'at.deleted' => PageTranslation::NO,
                                ]);
                            },
                        ])
                        ->where([
                            'a.status' => Article::STATUS_ACTIVE,
                            'a.deleted' => Article::NO,
                            'a.type' => Article::TYPE_BLOG,
                            'at.language_id' => Yii::$app->language,
                        ]),
                    'pagination' => [
                        'pageSize' => 0,
                    ],
                ]);
            }
            
            // Activities page and data providers
            $activitiesPage = Page::findPageByRoute(['/activity/default/index']);
            $activitiesPageTranslations = [];
            if ($activitiesPage) {
                $activitiesPageTranslations = $activitiesPage->getPageTranslations()
                    ->where([
                        'language_id' => Yii::$app->language
                    ])
                    ->indexBy('language_id')
                    ->all();
            }
            
            $activityCategoriesDataProvider = null;
            if ($articleCategoryTableExists) {
                $activityCategoriesDataProvider = new ActiveDataProvider([
                    'query' => ArticleCategory::find()
                        ->alias('c')
                        ->select([
                            'c.*',
                        ])
                        ->joinWith([
                            'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                                return $query->andOnCondition([
                                    'ct.language_id' => Yii::$app->language,
                                    'ct.deleted' => PageTranslation::NO,
                                ]);
                            },
                        ])
                        ->where([
                            'c.status' => ArticleCategory::STATUS_ACTIVE,
                            'c.deleted' => ArticleCategory::NO,
                            'c.type' => ArticleCategory::TYPE_ACTIVITY,
                            'ct.language_id' => Yii::$app->language,
                        ]),
                    'pagination' => [
                        'pageSize' => 0,
                    ],
                ]);
            }
            
            $activitiesDataProvider = null;
            if ($articleTableExists) {
                $activitiesDataProvider = new ActiveDataProvider([
                    'query' => Article::find()
                        ->alias('a')
                        ->select([
                            'a.*',
                        ])
                        ->joinWith([
                            'articleTranslations at' => function (ActiveQuery $query) {
                                return $query->andOnCondition([
                                    'at.language_id' => Yii::$app->language,
                                    'at.deleted' => PageTranslation::NO,
                                ]);
                            },
                        ])
                        ->where([
                            'a.status' => Article::STATUS_ACTIVE,
                            'a.deleted' => Article::NO,
                            'a.type' => Article::TYPE_ACTIVITY,
                            'at.language_id' => Yii::$app->language,
                        ]),
                    'pagination' => [
                        'pageSize' => 0,
                    ],
                ]);
            }

            $items = [
                [
                    'dataProvider' => $pagesDataProvider,
                    'route' => function ($item, $model) {
                        return implode('/', array_filter([
                            $model->translation->slug
                        ]));
                    },
                    'options' => [
                        'changefreq' => Sitemap::CHANGEFREQ_YEARLY,
                        'priority' => '1.0',
                    ],
                ],
            ];
            
            // Add article categories if articles page exists and tables exist
            if ($articleCategoryTableExists && $articlesPage && !empty($articlesPageTranslations[Yii::$app->language]) && $articleCategoriesDataProvider) {
                $items[] = [
                    'dataProvider' => $articleCategoriesDataProvider,
                    'route' => function ($item, $model) use ($articlesPage, $articlesPageTranslations) {
                        return implode('/', array_filter([
                            $articlesPageTranslations[Yii::$app->language]->slug,
                            Inflector::slug(Yii::t($articlesPage->module, 'Category', [], Yii::$app->language)),
                            $model->translation->slug
                        ]));
                    },
                    'options' => [
                        'changefreq' => Sitemap::CHANGEFREQ_DAILY,
                        'priority' => '1.0',
                    ],
                ];
            }
            
            // Add articles if articles page exists and table exists
            if ($articleTableExists && $articlesPage && !empty($articlesPageTranslations[Yii::$app->language]) && $articlesDataProvider) {
                $items[] = [
                    'dataProvider' => $articlesDataProvider,
                    'route' => function ($item, $model) use ($articlesPage, $articlesPageTranslations) {
                        return implode('/', array_filter([
                            $articlesPageTranslations[Yii::$app->language]->slug,
                            $model->translation->slug
                        ]));
                    },
                    'options' => [
                        'changefreq' => Sitemap::CHANGEFREQ_DAILY,
                        'priority' => '1.0',
                        'lastmod' => 'updated_at',
                    ],
                ];
            }
            
            // Add activity categories if activities page exists and tables exist
            if ($articleCategoryTableExists && $activitiesPage && !empty($activitiesPageTranslations[Yii::$app->language]) && $activityCategoriesDataProvider) {
                $items[] = [
                    'dataProvider' => $activityCategoriesDataProvider,
                    'route' => function ($item, $model) use ($activitiesPage, $activitiesPageTranslations) {
                        return implode('/', array_filter([
                            $activitiesPageTranslations[Yii::$app->language]->slug,
                            Inflector::slug(Yii::t($activitiesPage->module, 'Category', [], Yii::$app->language)),
                            $model->translation->slug
                        ]));
                    },
                    'options' => [
                        'changefreq' => Sitemap::CHANGEFREQ_DAILY,
                        'priority' => '1.0',
                    ],
                ];
            }
            
            // Add activities if activities page exists and table exists
            if ($articleTableExists && $activitiesPage && !empty($activitiesPageTranslations[Yii::$app->language]) && $activitiesDataProvider) {
                $items[] = [
                    'dataProvider' => $activitiesDataProvider,
                    'route' => function ($item, $model) use ($activitiesPage, $activitiesPageTranslations) {
                        return implode('/', array_filter([
                            $activitiesPageTranslations[Yii::$app->language]->slug,
                            $model->translation->slug
                        ]));
                    },
                    'options' => [
                        'changefreq' => Sitemap::CHANGEFREQ_DAILY,
                        'priority' => '1.0',
                        'lastmod' => 'updated_at',
                    ],
                ];
            }

            return Sitemap::widget([
                'items' => $items,
            ]);
        }
    }
}

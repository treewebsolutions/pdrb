<?php

namespace common\models;

use tws\behaviors\DateTimeBehavior;
use tws\helpers\Url;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property int $id
 * @property string $image
 * @property string $icon
 * @property string $video
 * @property string $author
 * @property string $source
 * @property string $source_url
 * @property string $start_at
 * @property string $end_at
 * @property int $featured
 * @property int $views
 * @property int $type
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property ArticleCategoryHasArticle[] $articleCategoryHasArticles
 * @property ArticleCategory[] $articleCategories
 * @property ArticleTranslation[] $articleTranslations
 * @property ArticleTranslation $translation
 * @property Language[] $languages
 * @property ArticleHasFile[] $articleHasFiles
 * @property File[] $files
 * @property ArticleHasPicture[] $articleHasPictures
 * @property Picture[] $pictures
 * @property User $creator
 * @property User $updater
 *
 * @property string|null $imageUrl
 */
class Article extends CommonActiveRecord
{
    const TYPE_BLOG = 1;
    const TYPE_ACTIVITY = 2;

    /**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%article}}';
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function behaviors()
	{
		return [
			'BlameableBehavior' => [
				'class' => BlameableBehavior::class,
			],
			'TimestampBehavior' => [
				'class' => TimestampBehavior::class,
				'value' => (new \DateTime)->format('Y-m-d H:i:s'),
			],
			'DateTimeBehavior' => [
				'class' => DateTimeBehavior::class,
				'attributes' => ['start_at', 'end_at'],
			],
			'SoftDeleteBehavior' => [
				'class' => SoftDeleteBehavior::class,
				'softDeleteAttributeValues' => [
					'deleted' => static::YES,
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['featured', 'views', 'type', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
			[['start_at', 'end_at', 'created_at', 'updated_at'], 'default'],
			[['status'], 'required'],
			[['image', 'icon', 'video', 'author', 'source', 'source_url'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('label', 'ID'),
			'image' => Yii::t('label', 'Image'),
			'icon' => Yii::t('label', 'Icon'),
			'video' => Yii::t('label', 'Video'),
			'author' => Yii::t('label', 'Author'),
			'source' => Yii::t('label', 'Source'),
			'source_url' => Yii::t('label', 'Source Url'),
			'start_at' => Yii::t('label', 'Start At'),
			'end_at' => Yii::t('label', 'End At'),
			'featured' => Yii::t('label', 'Featured'),
			'views' => Yii::t('label', 'Views'),
			'type' => Yii::t('label', 'Type'),
			'created_by' => Yii::t('label', 'Created By'),
			'updated_by' => Yii::t('label', 'Updated By'),
			'created_at' => Yii::t('label', 'Created At'),
			'updated_at' => Yii::t('label', 'Updated At'),
			'status' => Yii::t('label', 'Status'),
			'deleted' => Yii::t('label', 'Deleted'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticleCategoryHasArticles()
	{
		return $this->hasMany(ArticleCategoryHasArticle::class, ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticleCategories()
	{
		return $this->hasMany(ArticleCategory::class, ['id' => 'article_category_id'])->viaTable('{{%article_category_has_article}}', ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticleTranslations()
	{
		return $this->hasMany(ArticleTranslation::class, ['article_id' => 'id']);
	}

	/**
	 * Gets the model translation.
	 *
	 * @param null|string $language
	 * @return null|ArticleTranslation
	 */
	public function getTranslation($language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}
		return ArrayHelper::index($this->articleTranslations, 'language_id')[$language];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getLanguages()
	{
		return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%article_translation}}', ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticleHasFiles()
	{
		return $this->hasMany(ArticleHasFile::class, ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getFiles()
	{
		return $this->hasMany(File::class, ['id' => 'file_id'])->viaTable('{{%article_has_file}}', ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticleHasPictures()
	{
		return $this->hasMany(ArticleHasPicture::class, ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPictures()
	{
		return $this->hasMany(Picture::class, ['id' => 'picture_id'])->viaTable('{{%article_has_picture}}', ['article_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getCreator()
	{
		return $this->hasOne(User::class, ['id' => 'created_by']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getUpdater()
	{
		return $this->hasOne(User::class, ['id' => 'updated_by']);
	}

    public function getTypeLabels()
    {
        return [
            self::TYPE_BLOG => Yii::t('common', 'Blog'),
            self::TYPE_ACTIVITY => Yii::t('common', 'Activity'),
        ];
    }

	/**
	 * Gets the imageUrl.
	 *
	 * @param bool $scheme
	 * @return string
	 */
	public function getImageUrl($scheme = false)
	{
		return Url::to("@uploads/article/{$this->id}/{$this->image}", $scheme);
	}

	/**
	 * Gets the videoEmbedUrl.
	 *
	 * @param bool|string $scheme
	 * @return null|string
	 */
	public function getVideoEmbedUrl($scheme = '')
	{
		if (!$this->video) {
			return null;
		}
		$url = Url::to($this->video, $scheme);

		if (strpos($url, 'youtube.com') !== false && strpos($url, 'watch?v=') !== false) {
			$url = str_replace('watch?v=', 'embed/', $url);
		}

		return $url;
	}

	/**
	 * Gets the previous article.
	 *
	 * @return array|static|\yii\db\ActiveRecord|null
	 */
	public function getPrevArticle($type = null)
	{
		return static::findSiblingArticle($this, 'prev', $type);
	}

	/**
	 * Gets the next article.
	 *
	 * @return array|static|\yii\db\ActiveRecord|null
	 */
	public function getNextArticle($type = null)
	{
		return static::findSiblingArticle($this, 'next', $type);
	}

	/**
	 * Finds the next article starting from a specific model.
	 *
	 * @param static $model
	 * @param string $direction
	 * @return array|\yii\db\ActiveRecord|static|null
	 */
	public static function findSiblingArticle($model, $direction = 'next', $type = null)
	{
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
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
				'a.status' => static::STATUS_ACTIVE,
				'a.deleted' => static::NO,
			])
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"));

		if ($direction === 'prev') {
			$query->orderBy(['a.created_at' => SORT_DESC]);
			$query->andFilterWhere(['<', 'a.created_at', $model->created_at]);
		} elseif ($direction === 'next') {
			$query->orderBy(['a.created_at' => SORT_ASC]);
			$query->andFilterWhere(['>', 'a.created_at', $model->created_at]);
		}

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

		return $query->limit(1)->one();
	}

	/**
	 * Finds the latest articles.
	 *
	 * @param int $limit The limit of the results.
	 * @return array|\yii\db\ActiveRecord|static[]|null
	 */
	public static function findLatestArticles($limit = 10, $type = null)
	{
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
			->alias('a')
			->joinWith([
				'articleTranslations at' => function (ActiveQuery $query) {
					$query->andOnCondition(['at.language_id' => Yii::$app->language]);
				},
			])
			->andWhere([
				'a.status' => static::STATUS_ACTIVE,
				'a.deleted' => static::NO,
			])
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"))
            ->andWhere([
                'IS NOT', 'at.content', null,
            ])
			->orderBy([
				'a.created_at' => SORT_DESC,
			]);

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

        return $query->limit($limit)->all();
	}

    /**
     * Finds the popular articles.
     *
     * @param int $limit The limit of the results.
     * @return array|\yii\db\ActiveRecord|static[]|null
     */
    public static function findPopularArticles($limit = 10, $type = null)
    {
	    $currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
            ->alias('a')
            ->joinWith([
                'articleTranslations at' => function (ActiveQuery $query) {
                    $query->andOnCondition(['at.language_id' => Yii::$app->language]);
                },
            ])
            ->andWhere([
                'a.status' => static::STATUS_ACTIVE,
                'a.deleted' => static::NO,
            ])
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"))
            ->andWhere([
                'IS NOT', 'at.content', null,
            ])
            ->orderBy([
                'a.views' => SORT_DESC,
            ]);

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

        return $query->limit($limit)->all();
    }

    /**
     * Finds the featured articles.
     *
     * @param int $limit The limit of the results.
     * @return array|\yii\db\ActiveRecord|static[]|null
     */
    public static function findFeaturedArticles($limit = 10, $type = null)
    {
	    $currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
            ->alias('a')
            ->joinWith([
                'articleTranslations at' => function (ActiveQuery $query) {
                    $query->andOnCondition(['at.language_id' => Yii::$app->language]);
                },
            ])
            ->andWhere([
                'a.status' => static::STATUS_ACTIVE,
                'a.featured' => static::YES,
                'a.deleted' => static::NO,
            ])
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"))
            ->orderBy([
                'a.created_at' => SORT_DESC,
            ]);

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

        return $query->limit($limit)->all();
    }

	/**
	 * Finds all active records for sitemap.
	 *
	 * @return static[]|array
	 */
	public static function findSitemapArticles($type = null)
	{
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');
        $query = static::find()
			->alias('a')
			->joinWith([
				'articleTranslations at',
			])
			->where([
				'a.status' => static::STATUS_ACTIVE,
				'a.deleted' => static::NO,
			])
	        ->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
	        ->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"));

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

        return $query->indexBy('id')->all();
	}

	/**
	 * Provides active records for RSS Feed.
	 *
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public static function provideRssArticles($type = null)
	{
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
			->alias('a')
			->joinWith([
				'articleTranslations at' => function (ActiveQuery $query) {
					$query->andOnCondition(['at.language_id' => Yii::$app->language]);
				},
			])
			->where([
				'a.status' => static::STATUS_ACTIVE,
				'a.deleted' => static::NO,
			])
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.start_at]]) <= 0 OR a.start_at IS NULL"))
			->andWhere(new Expression("TIMESTAMPDIFF(SECOND, '{$currentDate}', [[a.end_at]]) > 0 OR a.end_at IS NULL"))
			->orderBy([
				'a.created_at' => SORT_DESC,
			]);

        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }
        return $query;
	}

	/**
	 * Provides active records by filter criteria.
	 *
	 * @param null|int $category
	 * @param null|string $tag
	 * @param null|int $year
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public static function provideArticles($category = null, $tag = null, $year = null, $type = null)
	{
		$currentDate = (new \DateTime())->format('Y-m-d H:i:s');
		$query = static::find()
			->alias('a')
			->joinWith([
				'articleTranslations at' => function (ActiveQuery $query) {
					$query->andOnCondition(['at.language_id' => Yii::$app->language]);
				},
			])
			->where([
				'a.status' => static::STATUS_ACTIVE,
				'a.deleted' => static::NO,
                'at.language_id' => Yii::$app->language,
			])
            ->andWhere([
                '>', 'CHAR_LENGTH(at.content)', 20,
            ])
			->orderBy([
				'a.created_at' => SORT_DESC,
			]);

		if (!empty($category)) {
			$query
				->joinWith(['articleCategories c'])
				->andFilterWhere(['c.id' => $category]);
		}
		if (!empty($tag)) {
			$query
				->andWhere(new Expression('FIND_IN_SET(:tag, [[at.keywords]])'))
				->addParams(['tag' => $tag]);
		}
		if (!empty($year)) {
			$query->andWhere(['LIKE', 'a.created_at', $year]);
		}
        if (!empty($type)) {
            $query->andWhere(['a.type' => $type]);
        }

		return $query;
	}

	/**
	 * Counts articles by year.
	 *
	 * @return array|null
	 */
	public static function countArticlesByYear($type = null)
	{
		try {
            $query = static::find()
				->select([
					"DATE_FORMAT([[created_at]], '%Y') AS [[year]]",
					"COUNT([[id]]) AS [[total]]",
				])
				->andWhere([
					'status' => static::STATUS_ACTIVE,
					'deleted' => static::NO,
				]);

            if (!empty($type)) {
                $query->andWhere(['type' => $type]);
            }
            return $query->groupBy(new Expression("DATE_FORMAT([[created_at]], '%Y')"))
				->createCommand()
				->queryAll();
		} catch (Exception $e) {
			return [];
		}
	}
}

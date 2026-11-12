<?php

namespace common\models;

use tws\helpers\Url;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%article_category}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $leaf
 * @property string $image
 * @property string $icon
 * @property string $color
 * @property int $featured
 * @property int $type
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property ArticleCategory $parent
 * @property ArticleCategory[] $articleCategories
 * @property ArticleCategoryHasArticle[] $articleCategoryHasArticles
 * @property Article[] $articles
 * @property ArticleCategoryTranslation[] $articleCategoryTranslations
 * @property ArticleCategoryTranslation $translation
 * @property Language[] $languages
 * @property User $creator
 * @property User $updater
 */
class ArticleCategory extends CommonActiveRecord
{
    static $categories = [];

    const TYPE_BLOG = 1;
    const TYPE_ACTIVITY = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_category}}';
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
            'PositionBehavior' => [
                'class' => PositionBehavior::class,
                'positionAttribute' => 'sort_order',
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
            [['parent_id', 'leaf', 'featured', 'type', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_at', 'updated_at'], 'default'],
            [['status'], 'required'],
            [['image', 'icon', 'color'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('label', 'ID'),
            'parent_id' => Yii::t('label', 'Parent ID'),
            'leaf' => Yii::t('label', 'Leaf'),
            'image' => Yii::t('label', 'Image'),
            'icon' => Yii::t('label', 'Icon'),
            'color' => Yii::t('label', 'Color'),
            'featured' => Yii::t('label', 'Featured'),
            'type' => Yii::t('label', 'Type'),
            'sort_order' => Yii::t('label', 'Sort Order'),
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
    public function getParent()
    {
        return $this->hasOne(ArticleCategory::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getArticleCategories()
    {
        return $this->hasMany(ArticleCategory::class, ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getArticleCategoryHasArticles()
    {
        return $this->hasMany(ArticleCategoryHasArticle::class, ['article_category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])->viaTable('{{%article_category_has_article}}', ['article_category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getArticleCategoryTranslations()
    {
        return $this->hasMany(ArticleCategoryTranslation::class, ['article_category_id' => 'id']);
    }

    /**
     * Gets the model translation.
     *
     * @param null|string $language
     * @return null|ArticleCategoryTranslation
     */
    public function getTranslation($language = null)
    {
        if ($language === null) {
            $language = Yii::$app->language;
        }
        return ArrayHelper::index($this->articleCategoryTranslations, 'language_id')[$language];
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%article_category_translation}}', ['article_category_id' => 'id']);
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
     * @param string $glue
     * @param int $type
     * @return string
     * @throws \Throwable
     */
    public function getTreeName($glue = ' &rsaquo; ')
    {
        return implode($glue, ArrayHelper::getColumn(self::getTree($this->id), 'translation.title'));
    }

    /**
     * Gets the imageUrl.
     *
     * @return string
     */
    public function getImageUrl($scheme = false)
    {
        return Url::to("@uploads/article-category/{$this->id}/{$this->image}", $scheme);
    }

    /**
     * Finds all active records.
     *
     * @return static[]|array
     */
    public static function findAllArticleCategories($type = null)
    {
	    $query = static::find()
		    ->alias('c')
		    ->joinWith([
			    'articleCategoryTranslations ct' => function (ActiveQuery $query) {
				    $query->andOnCondition([
					    'ct.language_id' => Yii::$app->language,
					    'ct.deleted' => ArticleCategoryTranslation::NO,
				    ]);
			    },
		    ])
		    ->andWhere([
			    'c.status' => static::STATUS_ACTIVE,
			    'c.deleted' => static::NO,
		    ]);
	    if (!empty($type)) {
		    $query->andWhere(['c.type' => $type]);
	    }

	    return $query->orderBy(new Expression('[[c.sort_order]] IS NULL'))
		    ->addOrderBy(['c.sort_order' => SORT_ASC])
		    ->addOrderBy(['ct.title' => SORT_ASC])
		    ->indexBy('id')
		    ->all();
    }

    /**
     * Finds all active records.
     *
     * @param int $category_id
     * @param int $type
     * @return mixed
     * @throws \Throwable
     */
    public static function getTree($category_id)
    {
        $tree = [];
        if (!$category_id) {
            return $tree;
        }
        $model = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                },
            ])
            ->where([
                'c.deleted' => self::NO,
                'c.id' => $category_id,
            ])
            ->one();
        $tree[$model->id] = $model;
        if ($model->parent_id) {
            $tree = array_merge($tree, array_reverse(self::getTree($model->parent_id)));
        }
        return array_reverse($tree);
    }

    /**
     * Finds all active records.
     *
     * @param null $category_id
     * @param int $type
     * @return mixed
     * @throws \Throwable
     */
    public static function findAllAvailableArticleCategories($category_id, $type = null)
    {
        $parentIds = ArrayHelper::getColumn(self::getTree($category_id), 'id');
        $parentIds = array_diff($parentIds, [$category_id]);
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                },
            ])
            ->where([
                'c.status' => self::STATUS_ACTIVE,
                'c.deleted' => self::NO,
            ])
            ->andFilterWhere(['<>', 'c.id', $category_id])
            ->andFilterWhere(['IN', 'c.id', $parentIds]);
        if (!empty($type)) {
            $query->andWhere(['c.type' => $type]);
        }
        return $query->orderBy(new Expression('c.sort_order IS NULL'))
            ->addOrderBy(['c.sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Finds model by slug.
     *
     * @param string $slug
     * @return array|\yii\db\ActiveRecord|static|null
     */
    public static function findArticleCategoryBySlug($slug, $type = null)
    {
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                },
            ])
            ->andWhere([
                'c.status' => static::STATUS_ACTIVE,
                'c.deleted' => static::NO,
                'ct.slug' => $slug,
            ]);

		if (!empty($type)) {
			$query->andWhere([
				'c.type' => $type,
			]);
		}

	    return  $query->limit(1)
	    ->one();
    }

    /**
     * Finds model by slug.
     *
     * @param string $criteria
     * @param bool $asArray
     * @return array|static[]]
     */
    public static function findByCriteria($criteria, $asArray = false, $type = null)
    {
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) use ($criteria) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                    $query->andWhere(['LIKE', 'ct.title', $criteria]);
                },
            ])
            ->andWhere([
                'c.status' => static::STATUS_ACTIVE,
                'c.deleted' => static::NO,
            ]);
            if (!empty($type)) {
                $query->andWhere(['c.type' => $type]);
            }
        return $query->orderBy(new Expression('[[c.sort_order]] IS NULL'))
            ->addOrderBy(['c.sort_order' => SORT_ASC])
            ->asArray($asArray)
            ->indexBy('id')
            ->all();
    }

    /**
     * Finds all active records for sitemap.
     *
     * @return static[]|array
     */
    public static function findSitemapArticleCategories($type = null)
    {
        $query = static::find()
	        ->alias('c')
	        ->joinWith([
		        'articleCategoryTranslations ct',
	        ])
	        ->andWhere([
		        'c.status' => static::STATUS_ACTIVE,
		        'c.deleted' => static::NO,
	        ]);
	    if (!empty($type)) {
		    $query->andWhere(['c.type' => $type]);
	    }
		return $query->indexBy('id')->all();
    }

    /**
     * Treeview
     */
    public static function treeview($parent_id, $current = null, $status = null, $type = null, $params = [])
    {
        $categories = static::getCategoriesByParent($parent_id, $status, $type);
        $output = '';
        if ($categories) {
            $output  .= '';
        }
        foreach ($categories as $category) {
            $url = ['index', 'category' => $category->translation->slug];
            if ($params) {
                foreach ($params as $key => $value) {
                    if ($key && $value) {
                        $url = array_merge($url, [$key => $value]);
                    }
                }
            }
            $list_class = '';
            $uls = '';
            $ule = '';
            $divs = '';
            $dive = '';
            $btn = '';
            $children = self::treeview($category->id, $current, $status, $type, $params);
            if ($children) {
                $list_class .= 'has-subtree ';
                if (($current && in_array($category->id, ArrayHelper::getColumn(static::getParents($current, $status, $type), 'id'))) || ($current && $current == $category->id)) {
                    $list_class .= 'subtree-open ';
                }
                $uls .= '<ul class="subtree">';
                $ule .= '</ul>';
                $divs = '<div class="subtree-header">';
                $dive = '</div>';
                $btn = '<button type="button" class="btn btn-subtree-toggle">
								<span class="fa fa-plus"></span>
						</button>';
            } else {
                if ($current == $category->id) {
                    $list_class .= 'active ';
                }
            }
            $output .= '<li class="' . $list_class . '">';
            $output .= $divs;
            $output .= Html::a(ucfirst($category->translation->title), $url);
            $output .= $btn;
            $output .= $dive;
            $output .= $uls;
            $output .= $children;
            $output .= $ule;
            $output .= '</li>';
        }
        if ($categories) {
            $output .= '';
        }
        return $output;
    }

    /**
     * @param $parent_id
     * @param int $level
     * @param null $status
     * @param null $type
     * @return array
     */
    public static function getCategoriesByParent($parent_id, $status = null, $type = null)
    {
        $children = [];
        /** @var static[] $categories */
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                },
            ])
            ->where([
                'c.parent_id' => $parent_id,
                'c.deleted' => self::NO,
            ]);
        if ($status) {
            $query->andWhere([
                'c.status' => $status
            ]);
        }
	    if ($type) {
		    $query->andWhere([
			    'c.type' => $type
		    ]);
	    }
        $categories = $query->all();

        foreach ($categories as $category) {
            $children[] = $category;
        }

        return $children;
    }

    /**
     * @param $category_id
     * @param null $status
     * @param null $type
     * @return ArticleCategory[]
     */
    public static function getParents($category_id, $status = null, $type = null)
    {
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'articleCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ArticleCategoryTranslation::NO,
                    ]);
                },
            ])
            ->where([
                'c.id' => $category_id,
                'c.deleted' => self::NO,
            ]);
        if ($status) {
            $query->andWhere([
                'c.status' => $status
            ]);
        }
	    if ($type) {
		    $query->andWhere([
			    'c.type' => $type
		    ]);
	    }
        $parent = $query->one();
        if ($parent->parent_id) {
            array_push(static::$categories, $parent);
            static::getParents($parent->parent_id, $status, $type);
        }
        return static::$categories;
    }
}

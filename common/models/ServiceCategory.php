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
 * This is the model class for table "{{%service_category}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $leaf
 * @property string $image
 * @property string $icon
 * @property string $color
 * @property int $featured
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property ServiceCategory $parent
 * @property ServiceCategory[] $serviceCategories
 * @property ServiceCategoryHasService[] $serviceCategoryHasServices
 * @property Service[] $services
 * @property ServiceCategoryTranslation[] $serviceCategoryTranslations
 * @property ServiceCategoryTranslation $translation
 * @property Language[] $languages
 * @property User $creator
 * @property User $updater
 */
class ServiceCategory extends CommonActiveRecord
{
    static $categories = [];

    /**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%service_category}}';
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
			[['parent_id', 'leaf', 'featured', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['created_at', 'updated_at'], 'safe'],
			[['created_at', 'updated_at'], 'default'],
			[['status'], 'required'],
			[['image', 'icon', 'color'], 'string', 'max' => 255],
			[['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceCategory::class, 'targetAttribute' => ['parent_id' => 'id']],
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
		return $this->hasOne(ServiceCategory::class, ['id' => 'parent_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getServiceCategories()
	{
		return $this->hasMany(ServiceCategory::class, ['parent_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getServiceCategoryHasServices()
	{
		return $this->hasMany(ServiceCategoryHasService::class, ['service_category_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getServices()
	{
		return $this->hasMany(Service::class, ['id' => 'service_id'])->viaTable('{{%service_category_has_service}}', ['service_category_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getServiceCategoryTranslations()
	{
		return $this->hasMany(ServiceCategoryTranslation::class, ['service_category_id' => 'id']);
	}

	/**
	 * Gets the model translation.
	 *
	 * @param null|string $language
	 * @return null|ServiceCategoryTranslation
	 */
	public function getTranslation($language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}
		return ArrayHelper::index($this->serviceCategoryTranslations, 'language_id')[$language];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getLanguages()
	{
		return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%service_category_translation}}', ['service_category_id' => 'id']);
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
		return Url::to("@uploads/service-category/{$this->id}/{$this->image}", $scheme);
	}

	/**
	 * Finds all active records.
	 *
	 * @return static[]|array
	 */
	public static function findAllServiceCategories()
	{
		try {
			return static::getDb()->cache(function ($db) {
				return static::find()
					->alias('c')
					->joinWith([
						'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
							$query->andOnCondition([
								'ct.language_id' => Yii::$app->language,
								'ct.deleted' => ServiceCategoryTranslation::NO,
							]);
						},
					])
					->andWhere([
						'c.status' => static::STATUS_ACTIVE,
						'c.deleted' => static::NO,
					])
					->orderBy(new Expression('[[c.sort_order]] IS NULL'))
					->addOrderBy(['c.sort_order' => SORT_ASC])
					->addOrderBy(['ct.title' => SORT_ASC])
					->indexBy('id')
					->all();
			}, 0, new TagDependency(['tags' => __FUNCTION__]));
		} catch (\Throwable $e) {
			return [];
		}
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
                'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ServiceCategoryTranslation::NO,
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
    public static function findAllAvailableServiceCategories($category_id)
    {
        $parentIds = ArrayHelper::getColumn(self::getTree($category_id), 'id');
        $parentIds = array_diff($parentIds, [$category_id]);
        return static::find()
            ->alias('c')
            ->joinWith([
                'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ServiceCategoryTranslation::NO,
                    ]);
                },
            ])
            ->where([
                'c.status' => self::STATUS_ACTIVE,
                'c.deleted' => self::NO,
            ])
            ->andFilterWhere(['<>', 'c.id', $category_id])
            ->andFilterWhere(['IN', 'c.id', $parentIds])
            ->orderBy(new Expression('c.sort_order IS NULL'))
            ->addOrderBy(['c.sort_order' => SORT_ASC])
            ->all();
    }

	/**
	 * Finds model by slug.
	 *
	 * @param string $slug
	 * @return array|\yii\db\ActiveRecord|static|null
	 */
	public static function findServiceCategoryBySlug($slug)
	{
		return static::find()
			->alias('c')
			->joinWith([
				'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
					$query->andOnCondition([
						'ct.language_id' => Yii::$app->language,
						'ct.deleted' => ServiceCategoryTranslation::NO,
					]);
				},
			])
			->andWhere([
				'c.status' => static::STATUS_ACTIVE,
				'c.deleted' => static::NO,
				'ct.slug' => $slug,
			])
			->limit(1)
			->one();
	}

    /**
     * Finds model by slug.
     *
     * @param string $criteria
     * @param bool $asArray
     * @return array|static[]]
     */
    public static function findByCriteria($criteria, $asArray = false)
    {
        return static::find()
            ->alias('c')
            ->joinWith([
                'serviceCategoryTranslations ct' => function (ActiveQuery $query) use ($criteria) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ServiceCategoryTranslation::NO,
                    ]);
                    $query->andWhere(['LIKE', 'ct.title', $criteria]);
                },
            ])
            ->andWhere([
                'c.status' => static::STATUS_ACTIVE,
                'c.deleted' => static::NO,
            ])
            ->orderBy(new Expression('[[c.sort_order]] IS NULL'))
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
    public static function findSitemapServiceCategories()
    {
        return static::find()
            ->alias('c')
            ->joinWith([
                'serviceCategoryTranslations ct',
            ])
            ->andWhere([
                'c.status' => static::STATUS_ACTIVE,
                'c.deleted' => static::NO,
            ])
            ->indexBy('id')
            ->all();
    }

    /**
     * Treeview
     */
    public static function treeview($parent_id, $current = null, $status = null, $params = [])
    {
        $categories = static::getCategoriesByParent($parent_id, $status);
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
            $children = self::treeview($category->id, $current, $status, $params);
            if ($children) {
                $list_class .= 'has-subtree ';
                if (($current && in_array($category->id, ArrayHelper::getColumn(static::getParents($current, $status), 'id'))) || ($current && $current == $category->id)) {
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
     * @return array
     */
    public static function getCategoriesByParent($parent_id, $status = null)
    {
        $children = [];
        /** @var static[] $categories */
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ServiceCategoryTranslation::NO,
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
        $categories = $query->all();

        foreach ($categories as $category) {
            $children[] = $category;
        }

        return $children;
    }

    /**
     * @param $category_id
     * @param null $status
     * @return ServiceCategory[]
     */
    public static function getParents($category_id, $status = null)
    {
        $query = static::find()
            ->alias('c')
            ->joinWith([
                'serviceCategoryTranslations ct' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'ct.language_id' => Yii::$app->language,
                        'ct.deleted' => ServiceCategoryTranslation::NO,
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
        $parent = $query->one();
        if ($parent->parent_id) {
            array_push(static::$categories, $parent);
            static::getParents($parent->parent_id, $status);
        }
        return static::$categories;
    }
}

<?php

namespace common\models;

use tws\helpers\Url;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%picture}}".
 *
 * @property int $id
 * @property string $image
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
 * @property PictureTranslation[] $pictureTranslations
 * @property PictureTranslation $translation
 * @property Language[] $languages
 * @property PageHasPicture[] $pageHasPictures
 * @property Page[] $pages
 * @property ArticleHasPicture[] $articleHasPictures
 * @property Article[] $articles
 * @property User $creator
 * @property User $updater
 *
 * @property string|null $imageUrl
 */
class Picture extends CommonActiveRecord
{
    const TYPE_GENERAL = 0;
    const TYPE_PAGE = 1;
    const TYPE_ARTICLE = 2;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%picture}}';
	}

	/**
	 * @inheritdoc
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
			[['type', 'status'], 'required'],
			[['featured', 'type', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['created_at', 'updated_at'], 'safe'],
			[['created_at', 'updated_at'], 'default'],
			[['image'], 'string', 'max' => 255],
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
	public function getPictureTranslations()
	{
		return $this->hasMany(PictureTranslation::class, ['picture_id' => 'id']);
	}

	/**
	 * Gets the model translation.
	 *
	 * @param null|string $language
	 * @return null|PictureTranslation
	 */
	public function getTranslation($language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}
		return ArrayHelper::index($this->pictureTranslations, 'language_id')[$language];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getLanguages()
	{
		return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%picture_translation}}', ['picture_id' => 'id']);
	}

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getPageHasPictures()
    {
        return $this->hasMany(PageHasPicture::class, ['picture_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(Page::class, ['id' => 'page_id'])->viaTable('{{%page_has_picture}}', ['picture_id' => 'id']);
    }

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticleHasPictures()
	{
		return $this->hasMany(ArticleHasPicture::class, ['picture_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticles()
	{
		return $this->hasMany(Article::class, ['id' => 'article_id'])->viaTable('{{%article_has_picture}}', ['picture_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTeamHasPictures()
	{
		return $this->hasMany(TeamHasPicture::class, ['picture_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTeams()
	{
		return $this->hasMany(Team::class, ['id' => 'team_id'])->viaTable('{{%team_has_picture}}', ['picture_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getMemberHasPictures()
	{
		return $this->hasMany(MemberHasPicture::class, ['picture_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getMembers()
	{
		return $this->hasMany(Member::class, ['id' => 'member_id'])->viaTable('{{%member_has_picture}}', ['picture_id' => 'id']);
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
	 * Gets the imageUrl.
	 *
	 * @param bool $scheme
	 * @return string
	 */
	public function getImageUrl($scheme = false)
	{
		return Url::to("@uploads/picture/{$this->id}/{$this->image}", $scheme);
	}

    /**
     * Finds the featured pictures.
     *
     * @param int $limit The limit of the results.
     * @return array|\yii\db\ActiveRecord[]|static[]|null
     */
    public static function findFeaturedPictures($limit = null, $offset = null, $group = [])
    {
        $query = static::find()
            ->alias('p')
            ->joinWith([
                'pictureTranslations pt' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'pt.language_id' => Yii::$app->language,
                        'pt.deleted' => PictureTranslation::NO,
                    ]);
                },
            ])
            ->andWhere([
                'p.featured' => static::YES,
                'p.status' => static::STATUS_ACTIVE,
                'p.deleted' => static::NO,
            ])
            ->orderBy(new Expression('[[p.sort_order]] IS NULL'))
            ->addOrderBy(['p.sort_order' => SORT_ASC]);
        if ($limit) {
            $query->limit($limit);
        }
        if ($offset) {
            $query->offset($offset);
        }
        if (!empty($group)) {
            foreach ($group as $column) {
                $query->addGroupBy(['p.' . $column]);
            }
        }
        return $query->all();
    }

    /**
     * Provides active records by filter criteria.
     *
     * @param null|int $category
     * @param null|string $tag
     * @param null|array $group
     * @return \yii\db\ActiveQuery|CommonActiveQuery
     */
    public static function providePictures($type = null)
    {
        $query = static::find()
            ->alias('p')
            ->joinWith([
                'pictureTranslations pt' => function (ActiveQuery $query) {
                    $query->andOnCondition([
                        'pt.language_id' => Yii::$app->language,
                        'pt.deleted' => PictureTranslation::NO,
                    ]);
                },
            ])
            ->andWhere([
                'p.status' => static::STATUS_ACTIVE,
                'p.deleted' => static::NO,
	            'p.type' => $type,
            ])
            ->orderBy(new Expression('[[p.sort_order]] IS NULL'))
            ->addOrderBy(['p.sort_order' => SORT_ASC]);

		if ($type) {
			$query->andWhere([
				'p.type' => $type,
			]);
		}

        return $query;
    }
}

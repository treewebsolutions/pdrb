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
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%partner}}".
 *
 * @property int $id
 * @property string $image
 * @property string $url
 * @property int $type
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property PartnerTranslation[] $partnerTranslations
 * @property PartnerTranslation $translation
 * @property Language[] $languages
 * @property User $creator
 * @property User $updater
 *
 * @property string|null $imageUrl
 */
class Partner extends CommonActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%partner}}';
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
            [['type', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'required'],
            [['image', 'url'], 'string', 'max' => 255],
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
            'url' => Yii::t('label', 'Url'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerTranslations()
    {
        return $this->hasMany(PartnerTranslation::class, ['partner_id' => 'id']);
    }

	/**
	 * Gets the model translation.
	 *
	 * @param null|string $language
	 * @return null|PartnerTranslation
	 */
	public function getTranslation($language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}
		return ArrayHelper::index($this->partnerTranslations, 'language_id')[$language];
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%partner_translation}}', ['partner_id' => 'id']);
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
		return Url::to("@uploads/partner/{$this->id}/{$this->image}", $scheme);
	}

	/**
	 * Finds all active records.
	 *
	 * @return static[]|array
	 */
	public static function findAllPartners()
	{
		try {
			return static::getDb()->cache(function ($db) {
				return static::find()
					->alias('p')
					->joinWith([
						'partnerTranslations pt' => function (ActiveQuery $query) {
							$query->andOnCondition(['pt.language_id' => Yii::$app->language]);
						},
					])
					->where([
						'p.status' => static::STATUS_ACTIVE,
						'p.deleted' => static::NO,
					])
					->orderBy(new Expression('[[p.sort_order]] IS NULL'))
					->addOrderBy(['p.sort_order' => SORT_ASC])
					->addOrderBy(['pt.name' => SORT_ASC])
					->indexBy('id')
					->all();
			}, 0, new TagDependency(['tags' => __FUNCTION__]));
		} catch (\Throwable $e) {
			return [];
		}
	}
}

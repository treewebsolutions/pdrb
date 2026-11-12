<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%partner_translation}}".
 *
 * @property int $partner_id
 * @property string $language_id
 * @property string $name
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property string $content
 * @property int $deleted
 *
 * @property Partner $partner
 * @property Language $language
 */
class PartnerTranslation extends CommonActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%partner_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['partner_id', 'language_id', 'name'], 'required'],
            [['partner_id', 'deleted'], 'integer'],
            [['keywords', 'description', 'content'], 'string'],
            [['language_id'], 'string', 'max' => 5],
            [['name', 'slug'], 'string', 'max' => 255],
            [['partner_id', 'language_id'], 'unique', 'targetAttribute' => ['partner_id', 'language_id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Partner::class, 'targetAttribute' => ['partner_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'partner_id' => Yii::t('label', 'Partner ID'),
            'language_id' => Yii::t('label', 'Language ID'),
            'name' => Yii::t('label', 'Name'),
            'slug' => Yii::t('label', 'Slug'),
            'keywords' => Yii::t('label', 'Keywords'),
            'description' => Yii::t('label', 'Description'),
            'content' => Yii::t('label', 'Content'),
            'deleted' => Yii::t('label', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['language_id' => 'language_id']);
    }

	/**
	 * Gets the keywords as array.
	 *
	 * @param string $delimiter
	 * @return array
	 */
	public function getKeywordsList($delimiter = ',')
	{
		return $this->keywords ? explode($delimiter, $this->keywords) : [];
	}
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%member_translation}}".
 *
 * @property int $member_id
 * @property string $language_id
 * @property string $role
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property string $content
 * @property int $deleted
 *
 * @property Language $language
 * @property Member $member
 */
class MemberTranslation extends CommonActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
	    return [
		    [['member_id', 'language_id', 'role'], 'required'],
		    [['member_id', 'deleted'], 'integer'],
		    [['keywords', 'description', 'content'], 'string'],
		    [['language_id'], 'string', 'max' => 5],
		    [['role', 'slug'], 'string', 'max' => 255],
		    [['member_id', 'language_id'], 'unique', 'targetAttribute' => ['member_id', 'language_id']],
		    [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'language_id']],
		    [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::class, 'targetAttribute' => ['member_id' => 'id']],
	    ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => Yii::t('label', 'Member ID'),
            'language_id' => Yii::t('label', 'Language ID'),
            'role' => Yii::t('label', 'Role'),
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
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['language_id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::class, ['id' => 'member_id']);
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

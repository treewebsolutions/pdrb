<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%department_translation}}".
 *
 * @property int $department_id
 * @property string $language_id
 * @property string $name
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property string $content
 * @property int $deleted
 *
 * @property Department $department
 * @property Language $language
 */
class DepartmentTranslation extends CommonActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%department_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_id', 'language_id', 'name'], 'required'],
            [['department_id', 'deleted'], 'integer'],
            [['keywords', 'description', 'content'], 'string'],
            [['language_id'], 'string', 'max' => 5],
            [['name', 'slug'], 'string', 'max' => 255],
            [['department_id', 'language_id'], 'unique', 'targetAttribute' => ['department_id', 'language_id']],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::class, 'targetAttribute' => ['department_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'department_id' => Yii::t('label', 'Department ID'),
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
    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
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

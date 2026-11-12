<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%event_translation}}".
 *
 * @property int $event_id
 * @property string $language_id
 * @property string $title
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property string $content
 * @property int $deleted
 *
 * @property Event $event
 * @property Language $language
 */
class EventTranslation extends CommonActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%event_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'language_id', 'title'], 'required'],
            [['event_id', 'deleted'], 'integer'],
            [['keywords', 'description', 'content'], 'string'],
            [['language_id'], 'string', 'max' => 5],
            [['title', 'slug'], 'string', 'max' => 255],
            [['event_id', 'language_id'], 'unique', 'targetAttribute' => ['event_id', 'language_id']],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => Event::class, 'targetAttribute' => ['event_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'event_id' => Yii::t('label', 'Event ID'),
            'language_id' => Yii::t('label', 'Language ID'),
            'title' => Yii::t('label', 'Title'),
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
    public function getEvent()
    {
        return $this->hasOne(Event::class, ['id' => 'event_id']);
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

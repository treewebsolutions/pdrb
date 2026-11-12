<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article_has_picture}}".
 *
 * @property int $article_id
 * @property int $picture_id
 *
 * @property Picture $picture
 * @property Article $article
 */
class ArticleHasPicture extends CommonActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%article_has_picture}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['article_id', 'picture_id'], 'required'],
			[['article_id', 'picture_id'], 'integer'],
			[['article_id', 'picture_id'], 'unique', 'targetAttribute' => ['article_id', 'picture_id']],
			[['picture_id'], 'exist', 'skipOnError' => true, 'targetClass' => Picture::class, 'targetAttribute' => ['picture_id' => 'id']],
			[['article_id'], 'exist', 'skipOnError' => true, 'targetClass' => Article::class, 'targetAttribute' => ['article_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'article_id' => Yii::t('label', 'Article ID'),
			'picture_id' => Yii::t('label', 'Picture ID'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getPicture()
	{
		return $this->hasOne(Picture::class, ['id' => 'picture_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticle()
	{
		return $this->hasOne(Article::class, ['id' => 'article_id']);
	}
}

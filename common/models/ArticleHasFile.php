<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article_has_file}}".
 *
 * @property int $article_id
 * @property int $file_id
 *
 * @property File $file
 * @property Article $article
 */
class ArticleHasFile extends CommonActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%article_has_file}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['article_id', 'file_id'], 'required'],
			[['article_id', 'file_id'], 'integer'],
			[['article_id', 'file_id'], 'unique', 'targetAttribute' => ['article_id', 'file_id']],
			[['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
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
			'file_id' => Yii::t('label', 'File ID'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getFile()
	{
		return $this->hasOne(File::class, ['id' => 'file_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getArticle()
	{
		return $this->hasOne(Article::class, ['id' => 'article_id']);
	}
}

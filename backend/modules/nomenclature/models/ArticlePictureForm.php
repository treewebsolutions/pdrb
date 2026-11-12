<?php

namespace backend\modules\nomenclature\models;

use common\models\Language;
use common\models\Picture;
use common\models\PictureTranslation;
use common\models\Article;
use common\models\ArticleHasPicture;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use common\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

class ArticlePictureForm extends Picture
{
	/**
	 * @var int The Article model ID.
	 */
	public $article_id;

	/**
	 * @var UploadedFile The article image file.
	 */
	public $imageFile;

	/**
	 * @var array The multilingual title of the article picture.
	 */
	public $title = [];

	/**
	 * @var Article The Article model.
	 */
	private $_article;

	/**
	 * @var array Enable google translator.
	 */
	public $translator = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		if ($article = $this->getArticle()) {
			foreach ($article->articleTranslations as $articleTranslation) {
				$this->title[$articleTranslation->language_id] = $articleTranslation->title;
			}
		}
		$this->type = static::TYPE_ARTICLE;
		$this->status = static::STATUS_ACTIVE;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return ArrayHelper::merge(parent::rules(), [
			[['title'], 'required', 'when' => function () {
				return empty($this->title[Yii::$app->language]);
			}, 'whenClient' => 'function (attribute, value) {
        return attribute.$form.find("[name*=\"[title][' . Yii::$app->language . ']\"]").val() == "";
    	}'],
			[['title'], 'each', 'rule' => ['trim']],
			[['imageFile'], 'file', 'extensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'], 'maxSize' => Yii::$app->settings->get('maxFileSize'), 'maxFiles' => $this->isNewRecord ? 15 : 1, 'skipOnEmpty' => true],
			[['article_id'], 'exist', 'skipOnError' => true, 'targetClass' => Article::class, 'targetAttribute' => ['article_id' => 'id']],
			[['translator'], 'safe'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'article_id' => Yii::t('label', 'Article ID'),
			'imageFile' => $this->isNewRecord ? Yii::t('label', 'Images') : Yii::t('label', 'Image'),
			'title' => Yii::t('label', 'Title'),
			'translator' => Yii::t('label', 'Translator'),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return Model::scenarios();
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		parent::afterFind();

		$this->title = ArrayHelper::map($this->pictureTranslations, 'language_id', 'title');
	}

	/**
	 * Gets the Article model.
	 *
	 * @return Article|null
	 */
	public function getArticle()
	{
		if (!$this->_article) {
			$this->_article = Article::findOne([
				'id' => $this->article_id,
				'status' => Article::STATUS_ACTIVE,
				'deleted' => Article::NO,
			]);
		}
		return $this->_article;
	}

	/**
	 * Saves the translations.
	 *
	 * @return bool
	 */
	protected function savePictureTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
			}

			foreach (Language::findAllLanguages() as $language) {
				$pictureTranslation = PictureTranslation::findOne([
					'picture_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$pictureTranslation) {
					$pictureTranslation = new PictureTranslation();
					$pictureTranslation->language_id = $language->language_id;
				}

				if (!empty($this->translator)) {
					if (!empty($this->translator) && $language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$pictureTranslation->title = StringHelper::truncate($this->title[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->title[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->title[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
				} else {
					$pictureTranslation->title = $this->title[$language->language_id];
				}

				if ($pictureTranslation->title) {
					$this->link('pictureTranslations', $pictureTranslation);
				}
			}

			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * Saves the files.
	 *
	 * @return bool
	 */
	protected function saveFiles()
	{
		try {
			if (!($file = UploadedFile::getInstance($this, 'imageFile'))) {
				return true;
			}

			$dirPath = Yii::getAlias("@uploads/picture/{$this->id}");
			$oldFilePath = "{$dirPath}/{$this->oldAttributes['image']}";
			$fileName = StringHelper::truncate(implode('_', array_filter([
					Inflector::slug($this->translation->title),
					Yii::$app->security->generateRandomString(8),
				])), 255 - (mb_strlen($file->extension) + 1), '') . ".{$file->extension}";
			$filePath = "{$dirPath}/{$fileName}";

			FileHelper::createDirectory($dirPath);
			if (!$file->saveAs($filePath)) {
				throw new \Exception();
			}
			if (!$this->updateAttributes(['image' => $fileName])) {
				throw new \Exception();
			}
			if (is_file($oldFilePath) && $oldFilePath != $filePath) {
				FileHelper::unlink($oldFilePath);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Saves multiple records with their files.
	 *
	 * @return bool
	 */
	protected function saveMultipleWithFiles()
	{
		try {
			if (!($files = UploadedFile::getInstances($this, 'imageFile'))) {
				return true;
			}
			$article = $this->getArticle();

			foreach ($files as $file) {
				$model = new Picture();
				$model->image = '_blank';
				$model->type = Picture::TYPE_ARTICLE;
				$model->status = static::STATUS_ACTIVE;
				if (!$model->save()) {
					throw new \Exception();
				}
				$articleHasPicture = new ArticleHasPicture();
				$articleHasPicture->article_id = $article->id;
				$articleHasPicture->picture_id = $model->id;
				if (!$articleHasPicture->save()) {
					throw new \Exception();
				}

				$suffix = (' - ' . str_pad($model->sort_order, 3, '0', STR_PAD_LEFT));

				foreach (Language::findAllLanguages() as $language) {
					$modelTranslation = new PictureTranslation();
					$modelTranslation->picture_id = $model->id;
					$modelTranslation->language_id = $language->language_id;
					$modelTranslation->title = static::ensureTranslationValue($this->title, $language->language_id) . (count($files) > 1 ? $suffix : '');
					if (!$modelTranslation->save()) {
						$this->addErrors($modelTranslation->getErrors());
					}
				}

				$dirPath = Yii::getAlias("@uploads/picture/{$model->id}");
				$fileName = StringHelper::truncate(implode('_', array_filter([
					Inflector::slug($this->title[Yii::$app->language] ? ($this->title[Yii::$app->language] . $suffix) : $file->baseName),
					Yii::$app->security->generateRandomString(8),
				])), 255 - (mb_strlen($file->extension) + 1), '') . ".{$file->extension}";
				FileHelper::createDirectory($dirPath);
				if (!$file->saveAs("{$dirPath}/{$fileName}")) {
					throw new \Exception();
				}
				if (!$model->updateAttributes(['image' => $fileName])) {
					throw new \Exception();
				}
			}
			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function save($runValidation = true, $attributeNames = null)
	{
		$isNewRecord = $this->getIsNewRecord();
		$transaction = static::getDb()->beginTransaction();
		try {
			if ($isNewRecord = $this->getIsNewRecord()) {
				if (!$this->saveMultipleWithFiles()) {
					throw new \Exception();
				}
			} else {
				if (!parent::save($runValidation, $attributeNames)) {
					throw new \Exception();
				}
				if (!$this->savePictureTranslations()) {
					throw new \Exception();
				}
				if (!$this->saveFiles()) {
					throw new \Exception();
				}
			}
			$transaction->commit();
			return true;
		} catch(\Exception $e) {
			if ($isNewRecord) {
				$this->id = null;
				$this->setIsNewRecord(true);
			}
			$this->addError('', $this->getCustomErrorMessage($e));
			$transaction->rollBack();
			return false;
		}
	}
}

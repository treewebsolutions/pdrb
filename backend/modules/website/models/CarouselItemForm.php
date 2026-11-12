<?php

namespace backend\modules\website\models;

use common\components\ApplicationBootstrap;
use common\models\Language;
use common\models\CarouselItem;
use common\models\CarouselItemTranslation;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use common\helpers\Inflector;
use common\helpers\StringHelper;
use yii\web\UploadedFile;

class CarouselItemForm extends CarouselItem
{
	/**
	 * @var UploadedFile The carousel image file.
	 */
	public $imageFile;

    /**
     * @var UploadedFile The carousel video file.
     */
    public $attachmentFile;

	/**
	 * @var array The multilingual title of the carousel item.
	 */
	public $title = [];

	/**
	 * @var array The multilingual content of the carousel item.
	 */
	public $content = [];

	/**
	 * @var array The multilingual anchor of the carousel item.
	 */
	public $anchor = [];

	/**
	 * @var array The multilingual url of the carousel item.
	 */
	public $url = [];

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

		$this->status = static::STATUS_ACTIVE;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return ArrayHelper::merge(parent::rules(), [
			[['title', 'content', 'anchor', 'url'], 'each', 'rule' => ['trim']],
			[['title', 'content', 'anchor', 'url'], 'each', 'rule' => ['default', 'value' => null]],
			[['target'], 'default', 'value' => null],
			[['imageFile'], 'file', 'extensions' => Yii::$app->params['image.extensions'], 'mimeTypes' => Yii::$app->params['image.mimeTypes'], 'maxSize' => Yii::$app->settings->get('maxFileSize'), 'maxFiles' => 1, 'skipOnEmpty' => true],
            [['attachmentFile'], 'file', 'extensions' => ['mp4'], 'mimeTypes' => ['video/mp4'], 'maxSize' => 9 * 1024 * 1024, 'maxFiles' => 1, 'skipOnEmpty' => true],
            [['translator'], 'safe'],
        ]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'imageFile' => Yii::t('label', 'Image'),
			'attachmentFile' => Yii::t('label', 'Video'),
			'title' => Yii::t('label', 'Title'),
			'content' => Yii::t('label', 'Content'),
			'anchor' => Yii::t('label', 'Text'),
			'url' => Yii::t('label', 'URL'),
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

		$this->title = ArrayHelper::map($this->carouselItemTranslations, 'language_id', 'title');
		$this->content = ArrayHelper::map($this->carouselItemTranslations, 'language_id', 'content');
		$this->anchor = ArrayHelper::map($this->carouselItemTranslations, 'language_id', 'anchor');
		$this->url = ArrayHelper::map($this->carouselItemTranslations, 'language_id', 'url');
	}

	/**
	 * Saves the translations.
	 *
	 * @return bool
	 */
	protected function saveCarouselItemTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
			}

			foreach (Language::findAllLanguages() as $language) {
				$carouselItemTranslation = CarouselItemTranslation::findOne([
					'carousel_item_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$carouselItemTranslation) {
					$carouselItemTranslation = new CarouselItemTranslation();
					$carouselItemTranslation->carousel_item_id = $this->id;
					$carouselItemTranslation->language_id = $language->language_id;
				}

				if (!empty($this->translator)) {
					if ($language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$carouselItemTranslation->title = \common\helpers\StringHelper::truncate($this->title[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->title[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->title[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
					$carouselItemTranslation->anchor = \common\helpers\StringHelper::truncate($this->anchor[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->anchor[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->anchor[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
					$carouselItemTranslation->url = \common\helpers\StringHelper::truncate($this->url[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->url[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->url[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
					$parts = StringHelper::splitString($this->content[$defaultLanguage->language_id], 5000);
					$translations = [];
					foreach ($parts as $part) {
						$translation = html_entity_decode($part);
						$translation = $source && $target ? Yii::$app->translate->translate($source, $target, $translation)['data']['translations'][0]['translatedText'] : null;
						$translation = html_entity_decode($translation);
						$translation = ApplicationBootstrap::recursiveStripTags($translation);
						$translation = str_replace(['& nbsp;', '&nbsp;', '< / p>'], [' ', ' ', ''], $translation);
						$translations[] = $translation;
					}
					$carouselItemTranslation->content = $this->content[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->content[$language->language_id] : (!empty($translations) ? implode('.', $translations) : null);
				} else {
					$carouselItemTranslation->title = $this->title[$language->language_id];
					$carouselItemTranslation->anchor = $this->anchor[$language->language_id];
					$carouselItemTranslation->url = $this->url[$language->language_id];
					$carouselItemTranslation->content = $this->content[$language->language_id];
				}

				if ($carouselItemTranslation->title) {
					$this->link('carouselItemTranslations', $carouselItemTranslation);
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
            $imageFile = UploadedFile::getInstance($this, 'imageFile');
            $attachmentFile = UploadedFile::getInstance($this, 'attachmentFile');
            if (!$imageFile && !$attachmentFile) {
                return true;
            }
            $dirPath = Yii::getAlias("@uploads/carousel/{$this->carousel->id}");
            FileHelper::createDirectory($dirPath);
            if ($imageFile) {
                $fileName = StringHelper::truncate(implode('_', array_filter([
                        Inflector::slug($this->translation->title),
                        Yii::$app->security->generateRandomString(8),
                    ])), 255 - (mb_strlen($imageFile->extension) + 1), '') . ".{$imageFile->extension}";
                $filePath = "{$dirPath}/{$fileName}";
                if (!$imageFile->saveAs($filePath)) {
                    throw new \Exception();
                }
                $this->image = $fileName;
            }
            if ($attachmentFile) {
                $fileName = StringHelper::truncate(implode('_', array_filter([
                        Inflector::slug($this->translation->title),
                        Yii::$app->security->generateRandomString(8),
                    ])), 255 - (mb_strlen($attachmentFile->extension) + 1), '') . ".{$attachmentFile->extension}";
                $filePath = "{$dirPath}/{$fileName}";
                if (!$attachmentFile->saveAs($filePath)) {
                    throw new \Exception();
                }
                $this->video = $fileName;
            }
            $this->save(true, ['image', 'video']);
            return true;
        } catch (\Exception $e) {
	        $this->addError('', $this->getCustomErrorMessage($e));
            return false;
        }
    }

	/**
	 * Saves the model.
	 *
	 * @return bool
	 */
	public function saveModel()
	{
		$dbTransaction = static::getDb()->beginTransaction();
		try {
			if (!$this->save()) {
				throw new \Exception();
			}
			if (!$this->saveCarouselItemTranslations()) {
				throw new \Exception();
			}
			if (!$this->saveFiles()) {
				throw new \Exception();
			}
			$dbTransaction->commit();
			return true;
		} catch(\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			$dbTransaction->rollBack();
			return false;
		}
	}
}

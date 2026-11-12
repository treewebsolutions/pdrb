<?php

namespace backend\modules\nomenclature\models;

use common\components\ApplicationBootstrap;
use common\models\Language;
use common\models\Service;
use common\models\ServiceCategory;
use common\models\ServiceTranslation;
use common\helpers\StringHelper;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

class ServiceForm extends Service
{
	/**
	 * @var int|array The service category model ID.
	 */
	public $service_category_id;

	/**
	 * @var UploadedFile The service image file.
	 */
	public $imageFile;

	/**
	 * @var array The multilingual title of the service.
	 */
	public $title = [];

	/**
	 * @var array The multilingual keywords of the service.
	 */
	public $keywords = [];

	/**
	 * @var array The multilingual description of the service.
	 */
	public $description = [];

	/**
	 * @var array The multilingual content of the service.
	 */
	public $content = [];

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
			[['title'], 'required', 'when' => function () {
				return empty($this->title[Yii::$app->language]);
			}, 'whenClient' => 'function (attribute, value) {
				return attribute.$form.find("[name*=\"[title][' . Yii::$app->language . ']\"]").val() == "";
			}'],
			[['title', 'keywords', 'description', 'content'], 'each', 'rule' => ['trim']],
			[['keywords', 'description', 'content'], 'each', 'rule' => ['default', 'value' => null]],
			[['icon', 'video'], 'default', 'value' => null],
			[['video'], 'url'],
			[['imageFile'], 'file', 'extensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'], 'maxSize' => Yii::$app->settings->get('maxFileSize'), 'maxFiles' => 1, 'skipOnEmpty' => true],
			[['service_category_id'], 'each', 'rule' => ['exist', 'targetClass' => ServiceCategory::class, 'targetAttribute' => ['service_category_id' => 'id'], 'skipOnError' => true]],
            [['translator'], 'safe'],
        ]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'service_category_id' => Yii::t('label', 'Service Category'),
			'imageFile' => Yii::t('label', 'Image'),
			'title' => Yii::t('label', 'Title'),
			'keywords' => Yii::t('label', 'Keywords'),
			'description' => Yii::t('label', 'Description'),
			'content' => Yii::t('label', 'Content'),
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

		$this->service_category_id = ArrayHelper::getColumn($this->serviceCategories, 'id');
		$this->title = ArrayHelper::map($this->serviceTranslations, 'language_id', 'title');
		$this->keywords = ArrayHelper::map($this->serviceTranslations, 'language_id', 'keywordsList');
		$this->description = ArrayHelper::map($this->serviceTranslations, 'language_id', 'description');
		$this->content = ArrayHelper::map($this->serviceTranslations, 'language_id', 'content');
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

			$dirPath = Yii::getAlias("@uploads/service/{$this->id}");
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
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * Saves the translations.
	 *
	 * @return bool
	 */
	protected function saveServiceTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
			}

			foreach (Language::findAllLanguages() as $language) {
				$serviceTranslation = ServiceTranslation::findOne([
					'service_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$serviceTranslation) {
					$serviceTranslation = new ServiceTranslation();
					$serviceTranslation->language_id = $language->language_id;
				}

				if (!empty($this->translator)) {
					if (!empty($this->translator) && $language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$serviceTranslation->title = \common\helpers\StringHelper::truncate($this->title[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->title[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->title[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 240, '');
					$serviceTranslation->slug = \common\helpers\Inflector::slug($serviceTranslation->title) ?: null;
					$serviceTranslation->keywords = StringHelper::truncate($this->keywords[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? implode(',', (array) $this->keywords[$language->language_id]) : ($source && $target ? Yii::$app->translate->translate($source, $target, implode(',', (array) $this->keywords[Yii::$app->language]))['data']['translations'][0]['translatedText'] : null), 255, '');
					$serviceTranslation->description = StringHelper::truncate($this->description[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->description[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->description[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
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
					$serviceTranslation->content = $this->content[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->content[$language->language_id] : (!empty($translations) ? implode('.', $translations) : null);
				} else {
					$serviceTranslation->title = $this->title[$language->language_id];
					$serviceTranslation->slug = Inflector::slug($serviceTranslation->title) ?: null;
					$serviceTranslation->keywords = $this->keywords[$language->language_id] ? implode(',', (array) $this->keywords[$language->language_id]) : null;
					$serviceTranslation->description = $this->description[$language->language_id];
					$serviceTranslation->content = $this->content[$language->language_id];
				}

				if ($serviceTranslation->title) {
					$this->link('serviceTranslations', $serviceTranslation);
				}
			}

			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

    /**
     * Links the Category models.
     *
     * @return bool
     * @throws \Throwable
     */
    protected function linkServiceCategories()
    {
        try {
            $this->unlinkAll('serviceCategories', true);
            if (empty($this->service_category_id)) {
                return true;
            }
            $ids = [];
            foreach ($this->service_category_id as $id) {
                $ids = array_merge($ids, ArrayHelper::getColumn(ServiceCategory::getTree($id), 'id'));
            }
            $ids = array_unique($ids);
            if (empty($ids)) {
                return true;
            }
            $categories = ServiceCategory::findAll([
                'id' => $ids,
                'status' => ServiceCategory::STATUS_ACTIVE,
                'deleted' => ServiceCategory::NO,
            ]);
            foreach ($categories as $category) {
                $this->link('serviceCategories', $category);
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
		$transaction = static::getDb()->beginTransaction();
		try {
			if (!parent::save($runValidation, $attributeNames)) {
				throw new \Exception();
			}
			if (!$this->saveFiles()) {
				throw new \Exception();
			}
			if (!$this->saveServiceTranslations()) {
				throw new \Exception();
			}
			if (!$this->linkServiceCategories()) {
				throw new \Exception();
			}
			$transaction->commit();
			return true;
		} catch(\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			$transaction->rollBack();
			return false;
		}
	}
}

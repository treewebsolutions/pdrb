<?php

namespace backend\modules\website\models;

use common\components\ApplicationBootstrap;
use common\models\Language;
use common\models\MenuItem;
use common\models\Page;
use common\models\PageTranslation;
use common\helpers\StringHelper;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class PageForm extends Page
{
	/**
	 * @var array The multilingual title of the page.
	 */
	public $title = [];

	/**
	 * @var array The multilingual slug of the page.
	 */
	public $slug = [];

	/**
	 * @var array The multilingual keywords of the page.
	 */
	public $keywords = [];

	/**
	 * @var array The multilingual description of the page.
	 */
	public $description = [];

	/**
	 * @var array The multilingual content of the page.
	 */
	public $content = [];

    /**
     * @var UploadedFile The image file.
     */
    public $imageFile;

    /**
     * @var UploadedFile The cover file.
     */
    public $coverFile;

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

		$this->controller = 'site';
		$this->action = 'page';
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
			[['module'], 'default', 'value' => null],
			[['title', 'slug', 'keywords', 'description', 'content'], 'each', 'rule' => ['trim']],
			[['keywords', 'description', 'content'], 'each', 'rule' => ['default', 'value' => null]],
            [['imageFile', 'coverFile'], 'file', 'extensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'], 'maxSize' => Yii::$app->settings->get('maxFileSize'), 'maxFiles' => 1, 'skipOnEmpty' => true],
            [['translator'], 'safe'],
        ]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'title' => Yii::t('label', 'Title'),
			'slug' => Yii::t('label', 'Slug'),
			'keywords' => Yii::t('label', 'Keywords'),
			'description' => Yii::t('label', 'Description'),
			'content' => Yii::t('label', 'Content'),
            'imageFile' => Yii::t('label', 'Image'),
            'coverFile' => Yii::t('label', 'Cover'),
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

		$this->title = ArrayHelper::map($this->pageTranslations, 'language_id', 'title');
		$this->slug = ArrayHelper::map($this->pageTranslations, 'language_id', 'slug');
		$this->keywords = ArrayHelper::map($this->pageTranslations, 'language_id', 'keywordsList');
		$this->description = ArrayHelper::map($this->pageTranslations, 'language_id', 'description');
		$this->content = ArrayHelper::map($this->pageTranslations, 'language_id', 'content');
	}

	/**
	 * Saves the translations.
	 *
	 * @return bool
	 */
	protected function savePageTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
				$shortCodes = []; $reverseShortCodes = [];
				foreach (array_keys(\common\models\Page::getShortCodeItems()) as $key) {
					$shortCodes[$key] = md5($key);
				}
				$reverseShortCodes = array_flip($shortCodes);
			} else {
				$shortCodes = \common\models\Page::getShortCodeItems();
			}

			foreach (Language::findAllLanguages() as $language) {
				$pageTranslation = PageTranslation::findOne([
					'page_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$pageTranslation) {
					$pageTranslation = new PageTranslation();
					$pageTranslation->page_id = $this->id;
					$pageTranslation->language_id = $language->language_id;
				}

				if (!empty($this->translator)) {
					if ($language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$pageTranslation->title = \common\helpers\StringHelper::truncate($this->title[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->title[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->title[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 240, '');
					if ($this->default == Page::NO) {
						$pageTranslation->slug = \common\helpers\Inflector::slug($pageTranslation->title) ?: null;
					} else {
						$pageTranslation->slug = '/';
					}
					$pageTranslation->keywords = StringHelper::truncate($this->keywords[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? implode(',', (array) $this->keywords[$language->language_id]) : ($source && $target ? Yii::$app->translate->translate($source, $target, implode(',', (array) $this->keywords[Yii::$app->language]))['data']['translations'][0]['translatedText'] : null), 255, '');
					$pageTranslation->description = StringHelper::truncate($this->description[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->description[$language->language_id] : ($source && $target ? strtr(Yii::$app->translate->translate($source, $target, strtr($this->description[Yii::$app->language], $shortCodes))['data']['translations'][0]['translatedText'], $reverseShortCodes) : null), 255, '');
					$parts = StringHelper::splitString(strtr($this->content[$defaultLanguage->language_id], $shortCodes), 5000);
					$translations = [];
					foreach ($parts as $part) {
						$translation = html_entity_decode($part);
						$translation = $source && $target ? Yii::$app->translate->translate($source, $target, $translation)['data']['translations'][0]['translatedText'] : null;
						$translation = html_entity_decode($translation);
						$translation = ApplicationBootstrap::recursiveStripTags($translation);
						$translation = str_replace(['& nbsp;', '&nbsp;', '< / p>'], [' ', ' ', ''], $translation);
						$translation = strtr($translation, $reverseShortCodes);
						$translations[] = $translation;
					}
					$pageTranslation->content = $this->content[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->content[$language->language_id] : (!empty($translations) ? implode('.', $translations) : null);
				} else {
					$pageTranslation->title = $this->title[$language->language_id];
					if ($this->default == Page::NO) {
						$pageTranslation->slug = \common\helpers\Inflector::slug($this->slug[$language->language_id] ?: $pageTranslation->title) ?: null;
					} else {
						$pageTranslation->slug = '/';
					}
					$pageTranslation->keywords = $this->keywords[$language->language_id] ? implode(',', (array) $this->keywords[$language->language_id]) : null;
					$pageTranslation->description = $this->description[$language->language_id];
					$pageTranslation->content = $this->content[$language->language_id];
				}

				if ($pageTranslation->title) {
					$this->link('pageTranslations', $pageTranslation);
				}
			}
			return true;
		} catch (\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

	/**
	 * Updates the menu item slugs.
	 *
	 * @return bool
	 */
	protected function updateMenuItemsSlugs()
	{
		/** @var MenuItem[] $menuItems */
		$menuItems = MenuItem::find()
			->alias('mi')
			->joinWith([
				'menuItemTranslations mit',
			])
			->andWhere([
				'OR',
				['=', 'mi.page_id', $this->id],
				['=', 'mi.parent_id', $this->id],
				['IS NOT', 'mi.parent_id', null], // TODO: maybe join with subquery that searches for subpages
			])
			->all();

		if (!$menuItems) {
			return true;
		}
$v = [];
		try {
//			foreach ($menuItems as $menuItem) {
//				foreach ($menuItem->menuItemTranslations as $menuItemTranslation) {
//					$v[] = $menuItem->getNestedSlug($menuItemTranslation->language_id);
//					if ($slug = $menuItem->getNestedSlug($menuItemTranslation->language_id)) {
//						$menuItemTranslation->slug = $slug;
//						if (!$menuItemTranslation->save()) {
//							$this->addErrors($menuItemTranslation->getErrors());
//							throw new \Exception($menuItemTranslation->getErrorSummary(false)[0]);
//						}
//					}
//				}
//			}
//echo '<pre>'; print_r($v); die();
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

            $dirPath = Yii::getAlias("@uploads/page/{$this->id}");
            $oldFilePath = "{$dirPath}/{$this->oldAttributes['image']}";
            $fileName = StringHelper::truncate(implode('_', array_filter([
                    \yii\helpers\Inflector::slug($this->translation->title),
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
     * Saves the cover.
     *
     * @return bool
     */
    protected function saveCover()
    {
        try {
            if (!($file = UploadedFile::getInstance($this, 'coverFile'))) {
                return true;
            }

            $dirPath = Yii::getAlias("@uploads/page/{$this->id}");
            $oldFilePath = "{$dirPath}/{$this->oldAttributes['cover']}";
            $fileName = StringHelper::truncate(implode('_', array_filter([
                    \yii\helpers\Inflector::slug($this->translation->title),
                    Yii::$app->security->generateRandomString(8),
                ])), 255 - (mb_strlen($file->extension) + 1), '') . ".{$file->extension}";
            $filePath = "{$dirPath}/{$fileName}";

            FileHelper::createDirectory($dirPath);
            if (!$file->saveAs($filePath)) {
                throw new \Exception();
            }
            if (!$this->updateAttributes(['cover' => $fileName])) {
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
			if (!$this->savePageTranslations()) {
				throw new \Exception();
			}
			if (!$this->updateMenuItemsSlugs()) {
				throw new \Exception();
			}
            if (!$this->saveFiles()) {
                throw new \Exception();
            }
            if (!$this->saveCover()) {
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

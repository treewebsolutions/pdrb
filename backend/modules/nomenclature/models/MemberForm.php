<?php

namespace backend\modules\nomenclature\models;

use common\components\ApplicationBootstrap;
use common\models\Language;
use common\models\Member;
use common\models\MemberTranslation;
use common\helpers\StringHelper;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

class MemberForm extends Member
{
	/**
	 * @var UploadedFile The member image file.
	 */
	public $imageFile;

	/**
	 * @var array The multilingual role of the member.
	 */
	public $role = [];

	/**
	 * @var array The multilingual keywords of the member.
	 */
	public $keywords = [];

	/**
	 * @var array The multilingual description of the member.
	 */
	public $description = [];

	/**
	 * @var array The multilingual content of the member.
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

		$this->status = static::STATUS_INACTIVE;	
		$this->type = static::TYPE_MEMBER;
		
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return ArrayHelper::merge(parent::rules(), [
			[['role', 'content'], 'each', 'rule' => ['trim']],
			[['content'], 'each', 'rule' => ['default', 'value' => null]],
			[['imageFile'], 'file', 'extensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'], 'maxSize' => Yii::$app->settings->get('maxFileSize'), 'maxFiles' => 1, 'skipOnEmpty' => true],
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
			'role' => Yii::t('label', 'Role'),
			'keywords' => Yii::t('label', 'Keywords'),
			'description' => Yii::t('label', 'Description'),
			'content' => Yii::t('label', 'Content'),
            'translator' => Yii::t('label', 'Translator'),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return ArrayHelper::merge(parent::attributes(), [
			'imageFile',
			'role',
			'keywords',
			'description',
			'content',
			'translator',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		parent::afterFind();
		
		$this->role = ArrayHelper::map($this->memberTranslations, 'language_id', 'role');
		$this->content = ArrayHelper::map($this->memberTranslations, 'language_id', 'content');
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

			$dirPath = Yii::getAlias("@uploads/member/{$this->id}");
			$oldFilePath = "{$dirPath}/{$this->oldAttributes['image']}";
			$fileName = StringHelper::truncate(implode('_', array_filter([
					Inflector::slug($this->fullName),
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
	protected function saveMemberTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
			}

			foreach (Language::findAllLanguages() as $language) {
				$memberTranslation = MemberTranslation::findOne([
					'member_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$memberTranslation) {
					$memberTranslation = new MemberTranslation();
					$memberTranslation->language_id = $language->language_id;
				}

				if (!empty($this->translator)) {
					if (!empty($this->translator) && $language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$memberTranslation->role = \common\helpers\StringHelper::truncate($this->role[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->role[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->role[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 240, '');
					$memberTranslation->slug = \common\helpers\Inflector::slug($this->fullName) ?: null;
					$memberTranslation->keywords = StringHelper::truncate($this->keywords[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? implode(',', (array) $this->keywords[$language->language_id]) : ($source && $target ? Yii::$app->translate->translate($source, $target, implode(',', (array) $this->keywords[Yii::$app->language]))['data']['translations'][0]['translatedText'] : null), 255, '');
					$memberTranslation->description = StringHelper::truncate($this->description[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->description[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->description[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
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
					$memberTranslation->content = $this->content[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->content[$language->language_id] : (!empty($translations) ? implode('.', $translations) : null);
				} else {
					$memberTranslation->role = $this->role[$language->language_id];
					$memberTranslation->slug = Inflector::slug($this->fullName) ?: null;
					$memberTranslation->keywords = $this->keywords[$language->language_id] ? implode(',', (array) $this->keywords[$language->language_id]) : null;
					$memberTranslation->description = $this->description[$language->language_id];
					$memberTranslation->content = $this->content[$language->language_id];
				}

				if ($memberTranslation->role) {
					$this->link('memberTranslations', $memberTranslation);
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
     * @throws \Throwable
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
            if (!$this->saveMemberTranslations()) {
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

<?php

namespace backend\modules\nomenclature\models;

use common\components\ApplicationBootstrap;
use common\helpers\StringHelper;
use common\models\Language;
use common\models\Template;
use common\models\TemplateTranslation;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class EmailTemplateForm extends Template
{
    /**
     * @var array The multilingual name of the Template.
     */
    public $name;

    /**
     * @var array The multilingual subject of the Template.
     */
    public $subject;

    /**
     * @var array The multilingual content of the Template.
     */
    public $content;

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

        $this->type = static::TYPE_EMAIL;
        $this->status = static::STATUS_ACTIVE;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['variant'], 'required'],
            [['name'], 'required', 'when' => function ($model) {
                return empty($model->name[Yii::$app->language]);
            }, 'whenClient' => 'function (attribute, value) {
        return attribute.$form.find("[name*=\"[name][' . Yii::$app->language . ']\"]").val() == "";
    	}'],
            [['subject'], 'required', 'when' => function ($model) {
                return empty($model->name[Yii::$app->language]);
            }, 'whenClient' => 'function (attribute, value) {
        return attribute.$form.find("[name*=\"[subject][' . Yii::$app->language . ']\"]").val() == "";
    	}'],
            [['content'], 'required', 'when' => function ($model) {
                return empty($model->name[Yii::$app->language]);
            }, 'whenClient' => 'function (attribute, value) {
        return attribute.$form.find("[name*=\"[content][' . Yii::$app->language . ']\"]").val() == "";
    	}'],
            [['name', 'subject', 'content'], 'each', 'rule' => ['trim']],
            [['translator'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name' => Yii::t('label', 'Name'),
            'subject' => Yii::t('label', 'Subject'),
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

        $this->name = ArrayHelper::map($this->templateTranslations, 'language_id', 'name');
        $this->subject = ArrayHelper::map($this->templateTranslations, 'language_id', 'subject');
        $this->content = ArrayHelper::map($this->templateTranslations, 'language_id', 'content');
    }

	/**
	 * Saves the Template translations.
	 *
	 * @return bool
	 */
	protected function saveTemplateTranslations()
	{
		try {
			if (!empty($this->translator)) {
				$languages = ArrayHelper::getColumn(Yii::$app->translate->discover()['data']['languages'], 'language');
				$defaultLanguage = Language::findOne(['language_id' => Yii::$app->language]);
				$shortCodes = []; $reverseShortCodes = [];
				foreach (array_keys(\backend\modules\nomenclature\models\EmailTemplateForm::getShortCodeItems($this->variant)) as $key) {
					$shortCodes[$key] = md5($key);
				}
				$reverseShortCodes = array_flip($shortCodes);
			} else {
				$shortCodes = \backend\modules\nomenclature\models\EmailTemplateForm::getShortCodeItems($this->variant);
			}
			foreach (\common\models\Language::findAllLanguages() as $language) {
				$templateTranslation = TemplateTranslation::findOne([
					'template_id' => $this->id,
					'language_id' => $language->language_id,
				]);
				if (!$templateTranslation) {
					$templateTranslation = new TemplateTranslation();
					$templateTranslation->template_id = $this->id;
					$templateTranslation->language_id = $language->language_id;
				}
				if (!empty($this->translator)) {
					if (!empty($this->translator) && $language->language_id != Yii::$app->language && in_array($defaultLanguage->language, $languages) & in_array($language->language, $languages)) {
						$source = $defaultLanguage->language;
						$target = $language->language;
					}
					$templateTranslation->name = StringHelper::truncate($this->name[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->name[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, $this->name[Yii::$app->language])['data']['translations'][0]['translatedText'] : null), 255, '');
					$translation = StringHelper::truncate($this->subject[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->subject[$language->language_id] : ($source && $target ? Yii::$app->translate->translate($source, $target, strtr($this->subject[Yii::$app->language], $shortCodes))['data']['translations'][0]['translatedText'] : null), 255, '');
					$templateTranslation->subject = strtr($translation, $reverseShortCodes);
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
					$templateTranslation->content = $this->content[$language->language_id] && ($language->language_id == Yii::$app->language || !in_array(2, (array)$this->translator)) ? $this->content[$language->language_id] : (!empty($translations) ? implode('.', $translations) : null);
				} else {
					$templateTranslation->name = $this->name[$language->language_id];
					$templateTranslation->subject = $this->subject[$language->language_id];
					$templateTranslation->content = $this->content[$language->language_id];
				}

				if ($templateTranslation->name) {
					$this->link('templateTranslations', $templateTranslation);
				}
			}
			return true;
		} catch(\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			return false;
		}
	}

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $dbTransaction = static::getDb()->beginTransaction();
        try {
            if (!parent::save($runValidation, $attributeNames)) {
                throw new \Exception();
            }
            if (!$this->saveTemplateTranslations()) {
                throw new \Exception();
            }
            $dbTransaction->commit();
            return $this;
        } catch(\Exception $e) {
	        $this->addError('', $this->getCustomErrorMessage($e));
            $dbTransaction->rollBack();
            return false;
        }
    }

    /**
     * Gets the short code items.
     *
     * @param null|int $variant
     * @return array
     */
    public static function getShortCodeItems($variant = null)
    {
        $shortCodeItems = [
            '{{APP_NAME}}' => Yii::t('label', 'App Name'),
            '{{APP_URL}}' => Yii::t('label', 'App URL'),
            '{{APP_LOGO_URL}}' => Yii::t('label', 'App Logo URL'),
            '{{APP_LOGO_ALT_URL}}' => Yii::t('label', 'App Logo Alternative URL'),
        ];

        switch ($variant) {
            case static::EMAIL_VARIANT_ACCOUNT_ACTIVATION:
                $shortCodeItems = array_merge($shortCodeItems, [
                    '{{FIRST_NAME}}' => Yii::t('label', 'First Name'),
                    '{{MIDDLE_NAME}}' => Yii::t('label', 'Middle Name'),
                    '{{LAST_NAME}}' => Yii::t('label', 'Last Name'),
                    '{{EMAIL}}' => Yii::t('label', 'Email'),
                    '{{PHONE}}' => Yii::t('label', 'Phone'),
                    '{{ACCOUNT_ACTIVATION_CODE}}' => Yii::t('label', 'Account Activation Code'),
                    '{{ACCOUNT_ACTIVATION_PAGE_URL}}' => Yii::t('label', 'Account Activation Page URL'),
                    '{{ACCOUNT_ACTIVATION_URL}}' => Yii::t('label', 'Account Activation URL'),
                ]);
                break;
            case static::EMAIL_VARIANT_PASSWORD_RESET:
                $shortCodeItems = array_merge($shortCodeItems, [
                    '{{FIRST_NAME}}' => Yii::t('label', 'First Name'),
                    '{{MIDDLE_NAME}}' => Yii::t('label', 'Middle Name'),
                    '{{LAST_NAME}}' => Yii::t('label', 'Last Name'),
                    '{{EMAIL}}' => Yii::t('label', 'Email'),
                    '{{PHONE}}' => Yii::t('label', 'Phone'),
                    '{{PASSWORD_RESET_CODE}}' => Yii::t('label', 'Password Reset Code'),
                    '{{PASSWORD_RESET_PAGE_URL}}' => Yii::t('label', 'Password Reset Page URL'),
                    '{{PASSWORD_RESET_URL}}' => Yii::t('label', 'Password Reset URL'),
                ]);
                break;
            case static::EMAIL_VARIANT_WELCOME:
                $shortCodeItems = array_merge($shortCodeItems, [
                    '{{FIRST_NAME}}' => Yii::t('label', 'First Name'),
                    '{{MIDDLE_NAME}}' => Yii::t('label', 'Middle Name'),
                    '{{LAST_NAME}}' => Yii::t('label', 'Last Name'),
                    '{{EMAIL}}' => Yii::t('label', 'Email'),
                    '{{PHONE}}' => Yii::t('label', 'Phone'),
                ]);
                break;
			case static::EMAIL_VARIANT_NEWSLETTER_SUBSCRIPTION:
				$shortCodeItems = array_merge($shortCodeItems, [
					'{{RECIPIENT_NAME}}' => Yii::t('label', 'Full Name'),
					'{{RECIPIENT_FIRST_NAME}}' => Yii::t('label', 'First Name'),
					'{{RECIPIENT_LAST_NAME}}' => Yii::t('label', 'Last Name'),
					'{{RECIPIENT_EMAIL}}' => Yii::t('label', 'Email'),
					'{{RECIPIENT_PHONE}}' => Yii::t('label', 'Phone'),
				]);
				break;
			case static::EMAIL_VARIANT_NEWSLETTER_CONFIRMATION:
				$shortCodeItems = array_merge($shortCodeItems, [
					'{{RECIPIENT_NAME}}' => Yii::t('label', 'Full Name'),
                    '{{RECIPIENT_FIRST_NAME}}' => Yii::t('label', 'First Name'),
                    '{{RECIPIENT_LAST_NAME}}' => Yii::t('label', 'Last Name'),
				]);
				break;
            default:
                break;
        }

        return $shortCodeItems;
    }
}

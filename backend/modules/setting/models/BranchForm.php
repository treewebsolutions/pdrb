<?php

namespace backend\modules\setting\models;

use common\models\Branch;
use common\models\Language;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class BranchForm extends Branch
{
	/**
	 * @var array The multilingual schedule, indexed by language_id.
	 */
	public $scheduleI18n = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->status = static::STATUS_ACTIVE;
		$this->country = Yii::$app->settings->get('defaultCountry');
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return ArrayHelper::merge(parent::rules(), [
			[['scheduleI18n'], 'each', 'rule' => ['trim']],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'scheduleI18n' => Yii::t('label', 'Schedule'),
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		parent::afterFind();

		$this->scheduleI18n = $this->getScheduleTranslations();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeValidate()
	{
		$schedule = [];
		foreach (Language::findAllLanguages() as $language) {
			$value = $this->scheduleI18n[$language->language_id] ?? null;
			if ($value !== null && $value !== '') {
				$schedule[$language->language_id] = $value;
			}
		}
		$this->schedule = $schedule ? Json::encode($schedule) : null;

		return parent::beforeValidate();
	}
}

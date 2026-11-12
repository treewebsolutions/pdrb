<?php

namespace backend\modules\nomenclature\models;

use common\models\Ranking;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class RankingForm extends Ranking
{
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
			[['competition_id', 'team_id'], 'required'],
			[['competition_id', 'team_id'], 'unique', 'targetAttribute' => ['competition_id', 'team_id'], 'message' => Yii::t('common', 'The combination of « {0} » and « {1} » has already been taken.', [Yii::t('label', 'Competition'), Yii::t('label', 'Team')])],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'competition_id' => Yii::t('label', 'Competition'),
			'stage_id' => Yii::t('label', 'Stage'),
			'team_id' => Yii::t('label', 'Team'),
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
			$transaction->commit();
			return true;
		} catch(\Exception $e) {
			$this->addError('', $this->getCustomErrorMessage($e));
			$transaction->rollBack();
			return false;
		}
	}
}

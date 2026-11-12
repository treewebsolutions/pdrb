<?php

namespace common\models;

use Yii;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%locality}}".
 *
 * @property int $id
 * @property string $county
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property int $status
 * @property int $deleted
 */
class Locality extends CommonActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%locality}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'SoftDeleteBehavior' => [
				'class' => SoftDeleteBehavior::class,
				'softDeleteAttributeValues' => [
					'deleted' => static::YES,
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['county', 'name', 'status'], 'required'],
			[['status', 'deleted'], 'integer'],
			[['county', 'name', 'latitude', 'longitude'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('label', 'ID'),
			'county' => Yii::t('label', 'County'),
			'name' => Yii::t('label', 'Name'),
			'latitude' => Yii::t('label', 'Latitude'),
			'longitude' => Yii::t('label', 'Longitude'),
			'status' => Yii::t('label', 'Status'),
			'deleted' => Yii::t('label', 'Deleted'),
		];
	}
}

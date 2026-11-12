<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\Expression;
use yii\helpers\Json;
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%branch}}".
 *
 * @property int $id
 * @property string $name
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $company
 * @property string $registration_number
 * @property string $tin
 * @property string $email
 * @property string $mobile_phone
 * @property string $fixed_phone
 * @property string $fax
 * @property string $schedule JSON: multilingual schedule indexed by language_id
 * @property string $address
 * @property string $street_name
 * @property string $street_number
 * @property string $locality
 * @property string $zip_code
 * @property string $county
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property User $creator
 * @property User $updater
 *
 * @property string $contactName
 * @property array $scheduleTranslations
 */
class Branch extends CommonActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%branch}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'BlameableBehavior' => [
				'class' => BlameableBehavior::class,
			],
			'TimestampBehavior' => [
				'class' => TimestampBehavior::class,
				'value' => (new \DateTime)->format('Y-m-d H:i:s'),
			],
			'PositionBehavior' => [
				'class' => PositionBehavior::class,
				'positionAttribute' => 'sort_order',
			],
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
			[['sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
			[['name', 'status'], 'required'],
			[['schedule'], 'string'],
			[['created_at', 'updated_at'], 'safe'],
			[['name', 'first_name', 'middle_name', 'last_name', 'company', 'registration_number', 'tin', 'email', 'mobile_phone', 'fixed_phone', 'fax', 'address', 'street_name', 'street_number', 'locality', 'zip_code', 'county', 'country', 'latitude', 'longitude'], 'string', 'max' => 255],
			[['name', 'first_name', 'middle_name', 'last_name', 'company', 'registration_number', 'tin', 'email', 'mobile_phone', 'fixed_phone', 'fax', 'address', 'street_name', 'street_number', 'locality', 'zip_code', 'county', 'country', 'latitude', 'longitude'], 'trim'],
			['email', 'email'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('label', 'ID'),
			'name' => Yii::t('label', 'Name'),
			'first_name' => Yii::t('label', 'First Name'),
			'middle_name' => Yii::t('label', 'Middle Name'),
			'last_name' => Yii::t('label', 'Last Name'),
			'company' => Yii::t('label', 'Company'),
			'registration_number' => Yii::t('label', 'Registration Number'),
			'tin' => Yii::t('label', 'Taxpayer Identification Number'),
			'email' => Yii::t('label', 'Email'),
			'mobile_phone' => Yii::t('label', 'Mobile Phone'),
			'fixed_phone' => Yii::t('label', 'Fixed Phone'),
			'fax' => Yii::t('label', 'Fax'),
			'schedule' => Yii::t('label', 'Schedule'),
			'address' => Yii::t('label', 'Address'),
			'street_name' => Yii::t('label', 'Street Name'),
			'street_number' => Yii::t('label', 'Street Number'),
			'locality' => Yii::t('label', 'City'),
			'zip_code' => Yii::t('label', 'Zip Code'),
			'county' => Yii::t('label', 'County'),
			'country' => Yii::t('label', 'Country'),
			'latitude' => Yii::t('label', 'Latitude'),
			'longitude' => Yii::t('label', 'Longitude'),
			'sort_order' => Yii::t('label', 'Sort Order'),
			'created_by' => Yii::t('label', 'Created By'),
			'updated_by' => Yii::t('label', 'Updated By'),
			'created_at' => Yii::t('label', 'Created At'),
			'updated_at' => Yii::t('label', 'Updated At'),
			'status' => Yii::t('label', 'Status'),
			'deleted' => Yii::t('label', 'Deleted'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getCreator()
	{
		return $this->hasOne(User::class, ['id' => 'created_by']);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getUpdater()
	{
		return $this->hasOne(User::class, ['id' => 'updated_by']);
	}

	/**
	 * Gets the contact person full name.
	 *
	 * @return string
	 */
	public function getContactName()
	{
		return implode(' ', array_filter([
			$this->last_name,
			$this->first_name,
			$this->middle_name,
		]));
	}

	/**
	 * Gets the decoded multilingual schedule, indexed by language_id.
	 *
	 * @return array
	 */
	public function getScheduleTranslations()
	{
		if (empty($this->schedule)) {
			return [];
		}
		try {
			return (array) Json::decode($this->schedule);
		} catch (\Exception $e) {
			return [];
		}
	}

	/**
	 * Gets the schedule in the given (or current) language.
	 *
	 * @param null|string $language
	 * @return null|string
	 */
	public function getScheduleText($language = null)
	{
		return $this->getScheduleTranslations()[$language ?: Yii::$app->language] ?? null;
	}

	/**
	 * Finds all active branches, ordered by sort order.
	 *
	 * @return static[]|array
	 */
	public static function findAllBranches()
	{
		try {
			return static::getDb()->cache(function ($db) {
				return static::find()
					->where([
						'status' => static::STATUS_ACTIVE,
						'deleted' => static::NO,
					])
					->orderBy(new Expression('[[sort_order]] IS NULL'))
					->addOrderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
					->indexBy('id')
					->all();
			}, 0, new TagDependency(['tags' => __FUNCTION__]));
		} catch (\Throwable $e) {
			return [];
		}
	}
}

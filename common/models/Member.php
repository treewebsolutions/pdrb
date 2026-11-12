<?php

namespace common\models;

use tws\behaviors\DateTimeBehavior;
use tws\helpers\Url;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%member}}".
 *
 * @property int $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $image
 * @property int $gender
 * @property string $date_of_birth
 * @property string $address
 * @property string $locality
 * @property string $country
 * @property int $type
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property MemberTranslation[] $memberTranslations
 * @property MemberTranslation $translation
 * @property Language[] $languages
 * @property Membership[] $memberships
 * @property Invoice[] $invoices
 * @property User $creator
 * @property User $updater
 *
 * @property string fullName
 * @property string fullNameInitials
 * @property string shortName
 * @property string|null $imageUrl
 */
class Member extends CommonActiveRecord
{

	const TYPE_FOUNDER = 1;
	const TYPE_CONTRIBUTOR = 2;
	const TYPE_MEMBER = 3;
	const TYPE_VOLUNTEER = 4;
	const TYPE_DISTINGUISHED = 5;
	const TYPE_SUPPORTER = 6;
	const TYPE_SYMPATHIZER = 7;
	/** @internal Live payment test tier (1 EUR); only offered to superAdmin on membership form */
	const TYPE_SUPERADMIN_TEST = 8;

	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

	/**
	 * @inheritdoc
	 * @throws \Exception
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
			'DateTimeBehavior' => [
				'class' => DateTimeBehavior::class,
				'attributes' => ['date_of_birth'],
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
		    [['gender', 'type', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
		    [['first_name', 'last_name', 'status'], 'required'],
		    [['date_of_birth', 'created_at', 'updated_at'], 'safe'],
		    [['first_name', 'middle_name', 'last_name', 'email', 'phone', 'image', 'address', 'locality', 'country'], 'string', 'max' => 255],
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
		    'first_name' => Yii::t('label', 'First Name'),
		    'middle_name' => Yii::t('label', 'Middle Name'),
		    'last_name' => Yii::t('label', 'Last Name'),
		    'email' => Yii::t('label', 'Email'),
		    'phone' => Yii::t('label', 'Phone'),
		    'image' => Yii::t('label', 'Image'),
		    'gender' => Yii::t('label', 'Gender'),
		    'date_of_birth' => Yii::t('label', 'Date Of Birth'),
		    'address' => Yii::t('label', 'Address'),
		    'locality' => Yii::t('label', 'Locality'),
		    'country' => Yii::t('label', 'Country'),
		    'type' => Yii::t('label', 'Type'),
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
	 * Model type labels.
	 *
	 * @return array
	 */
	public static function getTypeLabels()
	{
		return [
			static::TYPE_FOUNDER => Yii::t('member', 'Founder'),
			static::TYPE_CONTRIBUTOR => Yii::t('member', 'Contributor'),
			static::TYPE_MEMBER => Yii::t('member', 'Member'),
			static::TYPE_VOLUNTEER => Yii::t('member', 'Volunteer'),
			static::TYPE_DISTINGUISHED => Yii::t('member', 'Distinguished Member'),
			static::TYPE_SUPPORTER => Yii::t('member', 'Supporting Member'),
			static::TYPE_SYMPATHIZER => Yii::t('member', 'Sympathizing Member'),
			static::TYPE_SUPERADMIN_TEST => Yii::t('member', 'Test payment (superadmin)'),
		];
	}

	/**
	 * Annual membership fees in EUR, indexed by member type.
	 *
	 * @return array<int, int>
	 */
	public static function getAnnualFees()
	{
		return [
			static::TYPE_DISTINGUISHED => 120,
			static::TYPE_SUPPORTER => 50,
			static::TYPE_SYMPATHIZER => 12,
		];
	}

	/**
	 * Member types offered on the public membership application form (excludes founders).
	 *
	 * @return array<int, string>
	 */
	public static function getMembershipApplicationTypeLabels()
	{
		$fees = static::getAnnualFees();
		$currency = Yii::$app->formatter->currencyCode;

		return [
			static::TYPE_DISTINGUISHED => Yii::t('member', 'Distinguished Member') . ' — ' . $fees[static::TYPE_DISTINGUISHED] . ' ' . $currency . '/' . Yii::t('member', 'year'),
			static::TYPE_SUPPORTER => Yii::t('member', 'Supporting Member') . ' — ' . $fees[static::TYPE_SUPPORTER] . ' ' . $currency . '/' . Yii::t('member', 'year'),
			static::TYPE_SYMPATHIZER => Yii::t('member', 'Sympathizing Member') . ' — ' . $fees[static::TYPE_SYMPATHIZER] . ' ' . $currency . '/' . Yii::t('member', 'year'),
		];
	}

	/**
	 * Extra paid type for superadmin-only live Stripe testing (1 EUR).
	 *
	 * @return array<int, int>
	 */
	public static function getSuperadminTestAnnualFees()
	{
		return [
			static::TYPE_SUPERADMIN_TEST => 1,
		];
	}

	/**
	 * Single dropdown entry for {@see getSuperadminTestAnnualFees()} (membership form).
	 *
	 * @return array<int, string>
	 */
	public static function getSuperadminTestMembershipApplicationLabels()
	{
		$fees = static::getSuperadminTestAnnualFees();
		$currency = Yii::$app->formatter->currencyCode;

		return [
			static::TYPE_SUPERADMIN_TEST => Yii::t('member', 'Test payment (superadmin)') . ' — ' . $fees[static::TYPE_SUPERADMIN_TEST] . ' ' . $currency . '/' . Yii::t('member', 'year'),
		];
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberTranslations()
    {
        return $this->hasMany(MemberTranslation::class, ['member_id' => 'id']);
    }

	/**
	 * Gets the model translation.
	 *
	 * @param null|string $language
	 * @return null|MemberTranslation
	 */
	public function getTranslation($language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}
		return ArrayHelper::index($this->memberTranslations, 'language_id')[$language] ?? null;
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['language_id' => 'language_id'])->viaTable('{{%member_translation}}', ['member_id' => 'id']);
    }

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getMemberships()
	{
		return $this->hasMany(Membership::class, ['member_id' => 'id']);
	}

	/**
	 * Returns the latest active membership for this member, if any.
	 *
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getActiveMembership()
	{
		return $this->hasOne(Membership::class, ['member_id' => 'id'])
			->andOnCondition(['membership.status' => Membership::STATUS_ACTIVE])
			->andOnCondition(['membership.deleted' => Membership::NO])
			->orderBy(['membership.end_at' => SORT_DESC, 'membership.id' => SORT_DESC]);
	}

	/**
	 * @return \yii\db\ActiveQuery|CommonActiveQuery
	 */
	public function getInvoices()
	{
		return $this->hasMany(Invoice::class, ['member_id' => 'id']);
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
	 * Gets the fullName.
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return implode(' ', array_filter([
			$this->first_name,
			$this->middle_name,
			$this->last_name,
		]));
	}

	/**
	 * Gets the fullNameInitials.
	 *
	 * @return string
	 */
	public function getFullNameInitials()
	{
		return implode('', array_filter([
			substr($this->first_name, 0, 1),
			substr($this->last_name, 0, 1),
		]));
	}

	/**
	 * Gets the shortName.
	 *
	 * @return string
	 */
	public function getShortName()
	{
		return implode('. ', array_filter([
			substr($this->first_name, 0, 1),
			$this->last_name,
		]));
	}

	/**
	 * Gets the imageUrl.
	 *
	 * @param bool $scheme
	 * @return string
	 */
	public function getImageUrl($scheme = false)
	{
		return Url::to("@uploads/member/{$this->id}/{$this->image}", $scheme);
	}

	/**
	 * Finds all active records.
	 *
	 * @param int|null $limit The maximum number of members to return
	 * @param int|null $type The member type to filter by
	 * @return static[]|array
	 */
	public static function findAllMembers($limit = null, $type = null)
	{
		try {
			return static::getDb()->cache(function ($db) use ($limit, $type) {
				$query = static::find()
					->alias('m')
					->joinWith([
						'memberTranslations mt' => function (ActiveQuery $query) {
							$query->andOnCondition(['mt.language_id' => Yii::$app->language]);
						},
					])
					->where([
						'm.status' => static::STATUS_ACTIVE,
						'm.deleted' => static::NO,
					])
					->orderBy(new Expression('[[m.sort_order]] IS NULL'))
					->addOrderBy(['m.sort_order' => SORT_ASC])
					->addOrderBy(['m.type' => SORT_ASC]);

				if (!empty($type)) {
					$query->andWhere(['m.type' => $type]);
				}

				if (!empty($limit)) {
					$query->limit($limit);
				}

				return $query->indexBy('id')->all();
			}, 0, new TagDependency(['tags' => __FUNCTION__]));
		} catch (\Throwable $e) {
			return [];
		}
	}
}

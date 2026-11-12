<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii2tech\ar\position\PositionBehavior;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%ranking}}".
 *
 * @property int $id
 * @property int $competition_id
 * @property int $stage_id
 * @property int $team_id
 * @property int $matches
 * @property int $wins
 * @property int $draws
 * @property int $loses
 * @property int $scored
 * @property int $received
 * @property int $points
 * @property int $type
 * @property int $sort_order
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $deleted
 *
 * @property Competition $competition
 * @property Stage $stage
 * @property Team $team
 * @property User $creator
 * @property User $updater
 */
class Ranking extends CommonActiveRecord
{

	const TYPE_FOOTBALL = 1;
	const TYPE_HANDBALL = 2;

	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ranking}}';
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
            [['competition_id', 'stage_id', 'team_id', 'matches', 'wins', 'draws', 'loses', 'scored', 'received', 'points', 'type', 'sort_order', 'created_by', 'updated_by', 'status', 'deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'required'],
            [['competition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Competition::class, 'targetAttribute' => ['competition_id' => 'id']],
            [['stage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stage::class, 'targetAttribute' => ['stage_id' => 'id']],
            [['team_id'], 'exist', 'skipOnError' => true, 'targetClass' => Team::class, 'targetAttribute' => ['team_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('label', 'ID'),
            'competition_id' => Yii::t('label', 'Competition ID'),
            'stage_id' => Yii::t('label', 'Stage ID'),
            'team_id' => Yii::t('label', 'Team ID'),
            'matches' => Yii::t('label', 'Matches'),
            'wins' => Yii::t('label', 'Wins'),
            'draws' => Yii::t('label', 'Draws'),
            'loses' => Yii::t('label', 'Loses'),
            'scored' => Yii::t('label', 'Scored'),
            'received' => Yii::t('label', 'Received'),
            'points' => Yii::t('label', 'Points'),
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
	 * Model gender labels.
	 *
	 * @return array
	 */
	public static function getTypeLabels()
	{
		return [
			static::TYPE_FOOTBALL => Yii::t('label', 'Football'),
			static::TYPE_HANDBALL => Yii::t('label', 'Handball'),
		];
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompetition()
    {
        return $this->hasOne(Competition::class, ['id' => 'competition_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStage()
    {
        return $this->hasOne(Stage::class, ['id' => 'stage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeam()
    {
        return $this->hasOne(Team::class, ['id' => 'team_id']);
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
}

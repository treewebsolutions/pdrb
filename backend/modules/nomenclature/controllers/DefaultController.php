<?php

namespace backend\modules\nomenclature\controllers;

use backend\controllers\MainController;
use common\models\Template;
use Yii;
use yii\filters\AccessControl;

class DefaultController extends MainController
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}

	/**
	 * Displays index page.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		return $this->redirect(['service/index'],301);
	}

    /**
     * Searches for a specific template.
     *
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionSearchTemplate()
    {
        $queryParams = Yii::$app->request->get();
        $data = Template::find()
            ->alias('t')
            ->select([
                't.id',
                'tt.content',
            ])
            ->joinWith('templateTranslations tt', false)
            ->andWhere([
                't.status' => Template::STATUS_ACTIVE,
                't.deleted' => Template::NO,
                't.id' => $queryParams['value'],
                'tt.language_id' => 'de-DE',
            ])
            ->limit(1)
            ->createCommand()
            ->queryOne();

        return $this->asJson($data);
    }
}

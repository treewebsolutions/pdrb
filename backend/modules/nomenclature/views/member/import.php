<?php

/* @var $this yii\web\View */
/* @var $model common\models\Member */

use backend\modules\import\widgets\spreadsheetimport\SpreadsheetImport;
use backend\modules\nomenclature\models\MemberForm;
use yii\helpers\Html;
use tws\helpers\Url;

$this->title = Yii::t('common', 'Import {item}', ['item' => Yii::t('common', 'Member')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Members'),
		'url' => ['index'],
	],
	Yii::t('common', 'Import'),
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewMember'),
		'tag' => 'a',
		'url' => ['index'],
		'icon' => 'fa fa-list',
		'options' => [
			'class' => 'btn btn-sm btn-default',
			'title' => Yii::t('common', 'Members'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
];
?>

<?= SpreadsheetImport::widget([
	'model' => MemberForm::class,
	'excludedColumns' => ['id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'status', 'deleted', 'sort_order', 'image', 'imageFile', 'translator'],
	'returnUrl' => Url::to(['index']),
]) ?>

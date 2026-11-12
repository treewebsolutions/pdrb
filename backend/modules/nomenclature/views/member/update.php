<?php
/* @var $this yii\web\View */
/* @var $model common\models\Member */

use common\models\Member;
use tws\helpers\Url;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Update {item}', ['item' => Yii::t('common', 'Member')]);
$this->params['breadcrumbs'] = [
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewMember'),
		'tag' => 'a',
		'url' => ['index'],
		'icon' => 'fa fa-list',
		'options' => [
			'class' => 'btn btn-sm btn-default',
			'title' => Yii::t('common', 'List'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('createMember'),
		'tag' => 'a',
		'url' => ['create'],
		'icon' => 'fa fa-plus',
		'options' => [
			'class' => 'btn btn-sm btn-success',
			'title' => Yii::t('common', 'Create'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
];
?>

<?= $this->render('_form', [
	'model' => $model,
]) ?>


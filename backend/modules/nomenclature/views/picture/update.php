<?php
/* @var $this yii\web\View */
/* @var $model common\models\Picture */

use yii\helpers\Html;

$this->title = Yii::t('common', 'Update {item}', ['item' => Yii::t('common', 'Picture')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Pictures'),
		'url' => ['picture/index'],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewProduct'),
		'tag' => 'a',
		'url' => ['index', 'product_id' => $product_id],
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
		'visible' => Yii::$app->user->can('createProduct'),
		'tag' => 'a',
		'url' => ['create', 'product_id' => $product_id],
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

<?php
/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategory */

use yii\helpers\Html;

$this->title = Yii::t('common', 'Create {item}', ['item' => Yii::t('common', 'Activity Category')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Activity Categories'),
		'url' => ['index'],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewArticleCategory'),
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
];
?>

<?= $this->render('_form', [
	'model' => $model,
]) ?>



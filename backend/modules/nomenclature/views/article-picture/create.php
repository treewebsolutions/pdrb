<?php
/* @var $this yii\web\View */
/* @var $model common\models\Picture */

use yii\helpers\Html;

$article_id = Yii::$app->request->get('article_id');

$this->title = Yii::t('common', 'Create {item}', ['item' => Yii::t('common', 'Picture')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Articles'),
		'url' => ['article/index'],
	],
	[
		'label' => \common\models\Article::findOne($article_id)->translation->title,
		'url' => ['article/view', 'id' => $article_id],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewArticle'),
		'tag' => 'a',
		'url' => ['index', 'article_id' => $article_id],
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

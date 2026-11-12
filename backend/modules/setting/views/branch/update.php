<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\BranchForm */

$this->title = Yii::t('common', 'Update {item}', ['item' => Yii::t('common', 'Branch')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Branches'),
		'url' => ['index'],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewBranch'),
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
		'visible' => Yii::$app->user->can('viewBranch'),
		'tag' => 'a',
		'url' => ['view', 'id' => $model->id],
		'icon' => 'fa fa-eye',
		'options' => [
			'class' => 'btn btn-sm btn-info',
			'title' => Yii::t('common', 'View'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('createBranch'),
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

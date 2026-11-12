<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\BranchForm */

$this->title = Yii::t('common', 'Create {item}', ['item' => Yii::t('common', 'Branch')]);
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
];
?>

<?= $this->render('_form', [
	'model' => $model,
]) ?>

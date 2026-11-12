<?php
/* @var $this yii\web\View */
/* @var $model common\models\Picture */

use yii\helpers\Html;

$this->title = Yii::t('common', 'Create {item}', ['item' => Yii::t('common', 'Picture')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Pictures'),
		'url' => ['picture/index'],
	],
	$this->title,
];
?>

<?= $this->render('_form', [
	'model' => $model,
]) ?>

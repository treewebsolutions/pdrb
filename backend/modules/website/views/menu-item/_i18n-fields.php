<?php

/* @var $form backend\widgets\ActiveForm */
/* @var $model common\models\MenuItem */
/* @var $language common\models\Language */

use yii\helpers\Html;

$titleHint = [
	Html::tag('span', Yii::t('backend', 'The title of the menu item.')),
	Html::tag('span', Yii::t('backend', 'Leave it blank to use the selected page title.'), [
		'class' => 'page-selected-hint' . ($model->page_id ? '' : ' hidden'),
	]),
];
$urlHint = [
	Html::tag('span', Yii::t('backend', 'The URL may contain a custom URL hash value (e.g. #custom-hash).')),
	Html::tag('span', Yii::t('backend', 'Leave it blank to use the selected page URL.'), [
		'class' => 'page-selected-hint' . ($model->page_id ? '' : ' hidden'),
	]),
];
?>

<?= $form->field($model, "title[{$language->language_id}]", [
		'options' => [
			'class' => 'form-group required-mark-target' . ($language->language_id == Yii::$app->language && empty($model->page_id) ? ' required' : ''),
		],
	])
	->textInput()
	->hint(implode(' ', $titleHint)) ?>

<?= $form->field($model, "url[{$language->language_id}]")
	->input('url')
	->hint(implode(' ', $urlHint)) ?>

<?php
$slugHint = [
	Html::tag('span', Yii::t('backend', 'The URL slug for this menu item.')),
	Html::tag('span', Yii::t('backend', 'Leave it blank to auto-generate from parent items, or empty to clear it.'), [
		'class' => 'page-selected-hint' . ($model->page_id ? '' : ' hidden'),
	]),
];
?>
<?= $form->field($model, "slug[{$language->language_id}]")
	->textInput()
	->hint(implode(' ', $slugHint)) ?>

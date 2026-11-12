<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

\frontend\assets\AppAsset::register($this);
\common\widgets\gallery\GalleryAsset::register($this);

$bodyAttributes = array_merge_recursive($this->context->bodyAttributes, (array) $this->params['bodyAttributes']);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= mb_substr(Yii::$app->language, 0, 2) ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php $this->registerCsrfMetaTags() ?>
	<title><?= implode(' - ', array_filter([Html::encode($this->title), Html::encode(Yii::$app->name)])) ?></title>
	<?php $this->head() ?>
    <?= Yii::$app->settings->get('headContent', 'script') ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $this->render('_widgets') ?>

<?= $this->render('_header') ?>

<?= $content ?>

<?= $this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

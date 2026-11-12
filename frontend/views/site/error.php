<?php
/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use tws\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->title = Html::encode($message);
$this->params['breadcrumbs'][] = Html::encode($this->title);
?>

<!-- error-section -->
<section class="error-section centred">
	<div class="auto-container">
		<div class="inner-box">
			<figure class="image-box"><img src="<?= Url::to('@web/images/icons/error.png') ?>" alt=""></figure>
			<h2><?= Html::encode($name) ?></h2>
			<p><?= nl2br(Html::encode($message)) ?></p>
			<a href="<?= Url::to(['/site/index']) ?>" class="theme-btn"><?= Yii::t('frontend', 'Back to Home') ?></a>
		</div>
	</div>
</section>
<!-- error-section end -->

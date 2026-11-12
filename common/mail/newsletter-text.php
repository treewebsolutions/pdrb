<?php
/* @var $this yii\web\View */
/* @var $campaign \common\models\MarketingCampaign */
/* @var $recipient \common\models\MarketingRecipient */

use yii\helpers\Html;
?>
<?= Yii::t('label', 'Recipient') ?>: <?= $recipient->name ?: '&mdash;' ?>,
<?= Yii::t('label', 'Subject') ?>: <?= $campaign->translation->subject ?: '&mdash;' ?>,
<?= Yii::t('label', 'Content') ?>: <?= $campaign->translation->content ?: '&mdash;' ?>
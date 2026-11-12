<?php

/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\SocialNetworkSettingForm */
/* @var $form backend\widgets\ActiveForm */

use backend\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('backend', 'Social Networks Settings');
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Settings'),
		'url' => ['index'],
	],
	$this->title,
];
?>

<?php $form = ActiveForm::begin([
	'id' => mb_strtolower($model->formName()),
	'options' => [
		'novalidate' => true,
	],
	'validateOnType' => true,
]); ?>
<div class="form-body <?= Yii::$app->request->isAjax ? 'modal-content' : '' ?>">
	<?php if (Yii::$app->request->isAjax) : ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<div class="modal-title"><?= $this->title ?></div>
		</div>
	<?php endif; ?>

	<div class="form-fields <?= Yii::$app->request->isAjax ? 'modal-body' : '' ?>">
		<?php if ($model->hasErrors()): ?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<div class="alert-table">
					<div class="row">
						<div class="icon">
							<span class="glyphicon glyphicon-remove-sign"></span>
						</div>
						<?= $form->errorSummary($model, ['header' => '']) ?>
						<div class="close">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="row">
			<div class="col-md-6">
				<div class="panel blue-hoki">
					<div class="panel-title">
						<div class="panel-heading">Facebook</div>
					</div>
					<div class="panel-body">
						<?= $form->field($model, 'facebookAppId')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': 123456789101112',
							'data' => [
								'toggle-visibility' => '#' . Html::getInputId($model, 'enableFacebookCustomerChat') . '-form-group',
								'toggle-visibility-val' => ':empty',
							],
						]) ?>
						<?= $form->field($model, 'enableFacebookCustomerChat', [
							'options' => [
								'id' => Html::getInputId($model, 'enableFacebookCustomerChat') . '-form-group',
								'class' => $model->facebookAppId ? '' : 'hidden',
							],
						])->checkbox() ?>
						<?= $form->field($model, 'facebookPage')->textInput([
							'type' => 'url',
							'placeholder' => Yii::t('common', 'Example') . ': https://www.faceebook.com/demo',
						]) ?>
						<?= $form->field($model, 'instagramPage')->textInput([
							'type' => 'url',
							'placeholder' => Yii::t('common', 'Example') . ': https://www.instagram.com/demo',
						]) ?>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="panel blue-hoki">
					<div class="panel-title">
						<div class="panel-heading">Twitter</div>
					</div>
					<div class="panel-body">
						<?= $form->field($model, 'twitterPage')->textInput([
							'type' => 'url',
							'placeholder' => Yii::t('common', 'Example') . ': https://www.twitter.com/demo',
						]) ?>
						<?= $form->field($model, 'twitterSite')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': @demo1',
						]) ?>
						<?= $form->field($model, 'twitterCreator')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': @demo2',
						]) ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('common', 'Other') ?></div>
			</div>
			<div class="panel-body">
				<?= $form->field($model, 'youtubeChannel')->textInput([
					'type' => 'url',
					'placeholder' => Yii::t('common', 'Example') . ': https://www.youtube.com/user/demo',
				]) ?>
				<?= $form->field($model, 'linkedinPage')->textInput([
					'type' => 'url',
					'placeholder' => Yii::t('common', 'Example') . ': https://www.linkedin.com/in/demo',
				]) ?>
				<?= $form->field($model, 'pinterestPage')->textInput([
					'type' => 'url',
					'placeholder' => Yii::t('common', 'Example') . ': https://www.pinterest.com/demo',
				]) ?>
				<?= $form->field($model, 'tiktokPage')->textInput([
					'type' => 'url',
					'placeholder' => Yii::t('common', 'Example') . ': https://www.tiktok.com/@demo',
				]) ?>
			</div>
		</div>

	</div>

	<?php if (Yii::$app->request->isAjax) : ?>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('common', 'Cancel') ?></button>
			<?= Html::submitButton(Yii::t('common', 'Save'), ['class' => 'btn btn-success']) ?>
		</div>
	<?php else : ?>
		<div class="form-actions floating">
			<?= Html::submitButton('<span class="fa fa-check"></span>', [
				'class' => 'btn btn-xlg btn-fab btn-success',
				'title' => Yii::t('common', 'Save'),
				'data' => [
					'toggle' => 'tooltip',
				],
			]) ?>
		</div>
	<?php endif; ?>
</div>
<?php ActiveForm::end(); ?>

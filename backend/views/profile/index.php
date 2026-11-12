<?php
/* @var $this yii\web\View */
/* @var $form backend\widgets\ActiveForm */
/* @var $model backend\models\UserProfileForm */

use backend\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Html;
use tws\helpers\Url;

$this->title = Yii::t('common', 'Profile');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin([
	'id' => mb_strtolower($model->formName()),
	'options' => [
		'novalidate' => true,
		'class' => Yii::$app->request->isAjax ? 'modal-dialog modal-lg' : '',
	],
	'validateOnType' => true,
]); ?>
<div class="form-body <?= Yii::$app->request->isAjax ? 'modal-content' : '' ?>">
	<?php if (Yii::$app->request->isAjax): ?>
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
			<div class="col-md-12 col-lg-4">
				<?= $form->field($model, 'imageFile')->widget(FileInput::class, [
					'options' => [
						'accept' => 'image/*',
						'data' => [
							'operation-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
						],
					],
					'resizeImages' => false,
					'sortThumbs' => false,
					'purifyHtml' => false,
					'pluginOptions' => [
						'allowedFileExtensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
						'maxFileSize' => Yii::$app->settings->get('maxFileSize'),
						'dropZoneEnabled' => true,
						'showClose' => false,
						'showUpload' => false,
						'showCaption' => true,
						'showRemove' => true,
						'showPreview' => true,
						'fileActionSettings' => [
							'showDownload' => true,
							'showRemove' => true,
							'showUpload' => false,
							'showZoom' => true,
							'showDrag' => false,
						],
						'defaultPreviewContent' => Html::img($model->imageUrl ?: Url::to("@web/img/img-placeholder-user.png"), ['class' => 'img-responsive', 'alt' => '']),
						'initialPreview' => $model->image ? $model->imageUrl : false,
						'initialPreviewConfig' => [
							[
								'downloadUrl' => $model->imageUrl,
							],
						],
						'initialPreviewAsData' => true,
						'initialPreviewShowDelete' => true,
						'overwriteInitial' => true,
						'deleteUrl' => Url::to(['delete-file', 'id' => $model->id])
					],
				]) ?>
			</div>
		</div>

		<fieldset class="fieldset">
			<legend><?= Yii::t('common', 'Personal Data') ?></legend>
			<?= $form->field($model, 'gender')->inline()->radioList(\common\models\User::getGenderLabels()) ?>
			<div class="row">
				<div class="col-sm-4">
					<?= $form->field($model, 'first_name')->textInput() ?>
				</div>
				<div class="col-sm-4">
					<?= $form->field($model, 'middle_name')->textInput() ?>
				</div>
				<div class="col-sm-4">
					<?= $form->field($model, 'last_name')->textInput() ?>
				</div>
			</div>
		</fieldset>

		<fieldset class="fieldset">
			<legend><?= Yii::t('common', 'Account') ?></legend>
			<div class="row">
				<div class="col-sm-4">
					<?= $form->field($model, 'email')->input('email', ['disabled' => true]) ?>
				</div>
				<div class="col-sm-4">
					<?= $form->field($model, 'phone')->input('tel', ['disabled' => true]) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4">
					<?= $form->field($model, 'new_password')->passwordInput() ?>
				</div>
				<div class="col-sm-4">
					<?= $form->field($model, 'new_password_confirm')->passwordInput() ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4">
					<?= $form->field($model, 'role')->textInput(['disabled' => true]) ?>
				</div>
			</div>
		</fieldset>

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

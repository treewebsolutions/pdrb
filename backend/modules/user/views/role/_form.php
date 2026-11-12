<?php

/* @var $this yii\web\View */
/* @var $form backend\widgets\ActiveForm */
/* @var $model common\models\User */

use backend\modules\user\helpers\PermissionHelper;
use common\models\AuthItem;
use backend\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
	'id' => 'role-form',
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
				<div class="col-sm-12">
					<?= $form->field($model, 'description')->textInput()->label(Yii::t('label', 'Name')) ?>
				</div>
			</div>
			<label class="bold"><?= Yii::t('common', 'Permissions') ?></label>
			<div class="panel-group accordion scrollable" id="permissions-accordion" role="tablist" aria-multiselectable="true">
				<?php $i = 0; foreach (AuthItem::getFilteredPermissions() as $key => $val) : ?>
					<div class="panel blue-hoki">
						<div class="panel-heading" role="tab" id="heading-<?= $i ?>">
							<div class="panel-title panel-title-checkbox bold">
								<label class="mt-checkbox mt-checkbox-outline">
									<?= Html::checkbox('all', count(array_diff(PermissionHelper::extractPermissions($val), $model->permissions)) === 0, [
										'data' => [
											'mark-all' => ".check-{$i}",
										],
									]) ?>
									<span></span>
								</label>
								<a class="accordion-toggle accordion-toggle-styled" role="button" data-toggle="collapse" href="#collapse-<?= $i ?>" aria-expanded="true" aria-controls="collapse-<?= $i ?>">
									<?= $val['heading'] ?>
								</a>
							</div>
						</div>
						<div id="collapse-<?= $i ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading-<?= $i ?>">
							<div class="panel-body">
								<?= PermissionHelper::renderItems($form, $model, $i, $val) ?>
							</div>
						</div>
					</div>
				<?php $i++; endforeach; ?>
			</div>
		</div>

		<?php if (Yii::$app->request->isAjax): ?>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('common', 'Cancel') ?></button>
				<?= Html::submitButton(Yii::t('common', 'Save'), ['class' => 'btn btn-success']) ?>
			</div>
		<?php else: ?>
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

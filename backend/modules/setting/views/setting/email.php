<?php

/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\EmailSettingForm */
/* @var $form backend\widgets\ActiveForm */

use backend\widgets\ActiveForm;
use kartik\touchspin\TouchSpin;
use yii\helpers\Html;

$this->title = Yii::t('backend', 'Email Settings');
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
			<div class="col-sm-6">
				<?= $form->field($model, 'from')->textInput()->hint(Yii::t('backend', 'The email address from where messages will be sent.')) ?>
			</div>
			<div class="col-sm-6">
				<?= $form->field($model, 'replyTo')->textInput()->hint(Yii::t('backend', 'The email address to which messages will be sent.')) ?>
			</div>
		</div>

		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('backend', 'SMTP Configuration') ?></div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'username')->textInput()->hint(Yii::t('backend', 'SMTP username.')) ?>
					</div>
					<div class="col-sm-6">
						<?= $form->field($model, 'password')->passwordInput()->hint(Yii::t('backend', 'SMTP password.')) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'host')->textInput()->hint(Yii::t('backend', 'SMTP server. For the server of the current aplication you can use {0}.', ['<span class="label label-info">localhost</span>'])) ?>
					</div>
					<div class="col-sm-6">
						<?= $form->field($model, 'port')->widget(TouchSpin::class, [
							'pluginOptions' => [
								'min' => 1,
								'max' => PHP_INT_MAX,
								'step' => 1,
								'decimals' => 0,
								'boostat' => 5,
								'maxboostedstep' => 10,
								'verticalbuttons' => true,
							],
						])->hint(Yii::t('backend', 'SMTP port. Usually is {0}.', ['<span class="label label-info">25</span>'])) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'encryption')->textInput()->hint(Yii::t('backend', 'Usually {0} or {1} encryption.', ['<span class="label label-info">tls</span>', '<span class="label label-info">ssl</span>'])) ?>
					</div>
				</div>
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

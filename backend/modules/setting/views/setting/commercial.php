<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\CommercialSettingForm */
/* @var $form backend\widgets\ActiveForm */

use backend\modules\setting\models\CommercialSettingForm;
use backend\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('backend', 'Commercial Settings');
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Settings'),
		'url' => ['index'],
	],
	Yii::t('common', 'Commercial'),
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
		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('backend', 'Company API Integrations') ?></div>
			</div>
			<div class="panel-body">
				<div class="panel panel-default">
					<div class="panel-heading">
						<?= $form->field($model, 'defaultGateway', [
							'radioTemplate' => '{beginLabel}{input}{labelTitle}<span></span>{endLabel}{error}{hint}',
							'options' => [
								'tag' => false,
							],
						])->radio([
							'id' => Html::getInputId($model, 'defaultGateway') . '-' . CommercialSettingForm::GATEWAY_OPENAPI,
							'value' => CommercialSettingForm::GATEWAY_OPENAPI,
							'label' => 'Open API',
							'labelOptions' => [
								'class' => 'mt-radio mt-radio-outline m0',
							],
							'uncheck' => null,
						]) ?>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-sm-6">
								<?= $form->field($model, 'openApiBaseUrl')->input('url')->hint(Yii::t('backend', 'The API base URL.') . ' ' . Yii::t('common', 'Example') . ': https://api.openapi.ro/api') ?>
							</div>
						</div>
						<?= $form->field($model, 'openApiKey')->textarea(['rows' => 2])->hint(Yii::t('backend', 'The API key.') . ' ' . Yii::t('common', 'Example') . ': abcdefghijklmnopqrstuvwxyz') ?>
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

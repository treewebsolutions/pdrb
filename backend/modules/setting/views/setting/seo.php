<?php

/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\SeoSettingForm */
/* @var $form backend\widgets\ActiveForm */

use backend\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('backend', 'Seo Settings');
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
		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('backend', 'Analytics') ?></div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'googleAnalytics')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': UA-12345678-1',
						])->hint(Yii::t('common', 'Go to {0}', [Html::a('Google Analytics', 'https://analytics.google.com', ['target' => '_blank'])])) ?>
					</div>
					<div class="col-sm-6">
						<?= $form->field($model, 'googleVerification')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': Ab1CdEF2ghI3JkLm4nOpQ5Rst6uVW7XyZ',
						])->hint(Yii::t('common', 'Go to {0}', [Html::a('Google Search Console', 'https://search.google.com/search-console', ['target' => '_blank'])])) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'bingVerification')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': 123AbCdEF4ghI5JkLm6nOpQ7Rst8uVW9XyZ',
						])->hint(Yii::t('common', 'Go to {0}', [Html::a('Bing Web Master Tools', 'https://www.bing.com/webmaster', ['target' => '_blank'])])) ?>
					</div>
					<div class="col-sm-6">
						<?= $form->field($model, 'yandexVerification')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': 123AbCdEF4ghI5JkLm6nOpQ7Rst8uVW9XyZ',
						])->hint(Yii::t('common', 'Go to {0}', [Html::a('Yandex', 'https://webmaster.yandex.com/sites/', ['target' => '_blank'])])) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'pinterestVerification')->textInput([
							'placeholder' => Yii::t('common', 'Example') . ': 1abc2defg5hij6lm8nopqrs5uvwxyz',
						])->hint(Yii::t('common', 'Go to {0}', [Html::a('Pinterest', 'https://pinterest.com/settings/#claimWebsite', ['target' => '_blank'])])) ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('backend', 'Indexing') ?></div>
			</div>
			<div class="panel-body">
				<?= $form->field($model, 'allowRobotsCrawling')->checkbox([
					'data' => [
						'toggle-visibility' => '#' . Html::getInputId($model, 'robotsFileContent') . '-form-group',
					],
				]) ?>
				<?= $form->field($model, 'robotsFileContent', [
					'options' => [
						'id' => Html::getInputId($model, 'robotsFileContent') . '-form-group',
						'class' => $model->allowRobotsCrawling ? '' : 'hidden',
					],
				])->textarea([
					'rows' => 8,
				])->hint(Yii::t('backend', 'The content of the robots.txt file. Leave it blank to be created automatically.')) ?>
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

<?php
/* @var $this yii\web\View */
/* @var $model common\models\Article */

use common\models\Article;
use tws\helpers\Url;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Update {item}', ['item' => Yii::t('common', 'Activity')]);
$this->params['breadcrumbs'] = [
	[
		'label' => Yii::t('common', 'Activities'),
		'url' => ['index'],
	],
	$this->title,
];
$this->params['actions'] = [
	[
		'visible' => Yii::$app->user->can('viewArticle'),
		'tag' => 'a',
		'url' => ['index'],
		'icon' => 'fa fa-list',
		'options' => [
			'class' => 'btn btn-sm btn-default',
			'title' => Yii::t('common', 'List'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
	[
		'visible' => Yii::$app->user->can('createArticle'),
		'tag' => 'a',
		'url' => ['create'],
		'icon' => 'fa fa-plus',
		'options' => [
			'class' => 'btn btn-sm btn-success',
			'title' => Yii::t('common', 'Create'),
			'data' => [
				'toggle' => 'tooltip',
			],
		],
	],
];
?>

<?= $this->render('_form', [
	'model' => $model,
]) ?>

<div class="panel blue-hoki">
	<div class="panel-title">
		<div class="panel-heading">
			<?= Yii::t('label', 'Pictures') ?>
			<?php if ($showTrash = Yii::$app->settings->get('enableSoftDelete')): ?>
				<?php $showTrash = Yii::$app->request->get('deleted') == Article::YES; ?>
				<span class="page-links">
					<?= implode('', [
						Html::a(Yii::t('common', 'Current'), ['article-picture/index', 'article_id' => $model->id], ['class' => $showTrash ? '' : 'active']),
						Html::a(Yii::t('common', 'Trash'), ['article-picture/index', 'article_id' => $model->id, 'deleted' => Article::YES], ['class' => $showTrash ? 'active' : '']),
					]); ?>
				</span>
			<?php endif; ?>
		</div>
		<div class="panel-actions">
			<?php if (Yii::$app->user->can('restoreArticle') && $showTrash): ?>
				<?= Html::button('<span class="fa fa-undo"></span>', [
					'type' => 'button',
					'class' => 'btn btn-sm btn-success hidden',
					'title' => Yii::t('common', 'Restore'),
					'data' => [
						'toggle' => 'tooltip',
						'dt-bulk-operation' => 'restore',
						'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
						'dt-url' => Url::to(['article-picture/restore', 'article_id' => $model->id]),
						'dt-table' => '#dt-article-pictures',
					],
				]) ?>
			<?php endif; ?>
			<?php if (Yii::$app->user->can('deleteArticle')): ?>
				<?= Html::button('<span class="fa fa-trash"></span>', [
					'type' => 'button',
					'class' => 'btn btn-sm btn-danger hidden',
					'title' => $showTrash ? Yii::t('common', 'Delete Permanently') : Yii::t('common', 'Delete'),
					'data' => [
						'toggle' => 'tooltip',
						'dt-bulk-operation' => $showTrash ? 'delete-permanently' : 'delete',
						'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
						'dt-url' => Url::to(['article-picture/delete', 'article_id' => $model->id]),
						'dt-table' => '#dt-article-pictures',
					],
				]) ?>
			<?php endif; ?>
			<?php if (Yii::$app->user->can('createArticle')): ?>
				<?= Html::a('<span class="fa fa-plus"></span>', ['article-picture/create', 'article_id' => $model->id], [
					'type' => 'button',
					'class' => 'btn btn-sm btn-success',
					'title' => Yii::t('common', 'Create'),
					'data' => [
						'toggle' => 'tooltip',
						'popup-action' => '',
						'popup-done' => ['redrawDataTable' => '#dt-article-pictures'],
					],
				]) ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="panel-body">
		<?= $this->render('/article-picture/index') ?>
	</div>
</div>

<div class="panel blue-hoki">
	<div class="panel-title">
		<div class="panel-heading">
			<?= Yii::t('label', 'Files') ?>
			<?php if ($showTrash = Yii::$app->settings->get('enableSoftDelete')): ?>
				<?php $showTrash = Yii::$app->request->get('deleted') == Article::YES; ?>
				<span class="page-links">
					<?= implode('', [
						Html::a(Yii::t('common', 'Current'), ['update', 'id' => $model->id], ['class' => $showTrash ? '' : 'active']),
						Html::a(Yii::t('common', 'Trash'), ['update', 'id' => $model->id, 'deleted' => Article::YES], ['class' => $showTrash ? 'active' : '']),
					]); ?>
				</span>
			<?php endif; ?>
		</div>
		<div class="panel-actions">
			<?php if (Yii::$app->user->can('restoreArticle') && $showTrash): ?>
				<?= Html::button('<span class="fa fa-undo"></span>', [
					'type' => 'button',
					'class' => 'btn btn-sm btn-success hidden',
					'title' => Yii::t('common', 'Restore'),
					'data' => [
						'toggle' => 'tooltip',
						'dt-bulk-operation' => 'restore',
						'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
						'dt-url' => Url::to(['article-file/restore', 'article_id' => $model->id]),
						'dt-table' => '#dt-article-files',
					],
				]) ?>
			<?php endif; ?>
			<?php if (Yii::$app->user->can('deleteArticle')): ?>
				<?= Html::button('<span class="fa fa-trash"></span>', [
					'type' => 'button',
					'class' => 'btn btn-sm btn-danger hidden',
					'title' => $showTrash ? Yii::t('common', 'Delete Permanently') : Yii::t('common', 'Delete'),
					'data' => [
						'toggle' => 'tooltip',
						'dt-bulk-operation' => $showTrash ? 'delete-permanently' : 'delete',
						'dt-confirm' => Yii::t('common', 'Are you sure you want to perform this operation?'),
						'dt-url' => Url::to(['article-file/delete', 'article_id' => $model->id]),
						'dt-table' => '#dt-article-files',
					],
				]) ?>
			<?php endif; ?>
			<?php if (Yii::$app->user->can('createArticle')): ?>
				<?= Html::a('<span class="fa fa-plus"></span>', ['article-file/create', 'article_id' => $model->id], [
					'type' => 'button',
					'class' => 'btn btn-sm btn-success',
					'title' => Yii::t('common', 'Create'),
					'data' => [
						'toggle' => 'tooltip',
						'popup-action' => '',
						'popup-done' => ['redrawDataTable' => '#dt-article-files'],
					],
				]) ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="panel-body">
		<?= $this->render('/article-file/index') ?>
	</div>
</div>



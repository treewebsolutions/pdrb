<?php

namespace frontend\modules\search;

use Yii;
use yii\helpers\Inflector;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		Yii::configure($this, require __DIR__ . '/config/main.php');
	}

	/**
	 * @inheritdoc
	 */
	public function bootstrap($app)
	{
		Yii::setAlias("@{$this->id}", __DIR__);

		$app->i18n->translations[$this->id] = [
			'class' => 'yii\i18n\PhpMessageSource',
			'basePath' => "@{$this->id}/messages",
			'forceTranslation' => true,
			'fileMap' => [
				$this->id => 'i18n.php',
			],
		];
		
		if ($app instanceof \yii\web\Application) {
			if ($urlRules = $this->buildUrlRules()) {
				$app->getUrlManager()->addRules([$urlRules], false);
			}
		}
	}

	/**
	 * Builds the rules for all pages of this module.
	 *
	 * @return array|null
	 */
	protected function buildUrlRules()
	{
		$pages = \common\models\Page::findPagesByModule($this->id);
		
		// If no pages found in database, return null (module still works via /search/default/index)
		if (empty($pages)) {
			return null;
		}

		$urlRules = [];

		foreach ($pages as $page) {
			$route = "{$page->controller}/{$page->action}";

			// For index action, use empty pattern since prefix already contains the slug
			$urlRules[] = [
				'pattern' => '',
				'route' => $route,
			];
		}

		$prefix = reset($pages)->translation->slug;

		return [
			'class' => 'yii\web\GroupUrlRule',
			'routePrefix' => $this->id,
			'prefix' => $prefix,
			'rules' => $urlRules,
		];
	}
}

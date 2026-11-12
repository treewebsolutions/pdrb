<?php

namespace frontend\modules\payment;

use Yii;
use yii\helpers\Inflector;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
	/**
	 * @inheritdoc
	 */
	public $layout = 'main';

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
	 * @return array
	 */
	protected function buildUrlRules()
	{
		$pages = \common\models\Page::findPagesByModule($this->id);
		$urlRules = [
			[
				'pattern' => '',
				'route' => 'default/index',
			],
		];

		foreach ($pages as $page) {
			$pageTranslation = $page->getTranslation();

			if ($page->controller === 'default') {
				$urlRules = array_merge($urlRules, $this->buildDefaultUrlRules($page, $pageTranslation));
				continue;
			}

			$urlRules[] = [
				'pattern' => $pageTranslation->slug,
				'route' => "{$page->controller}/{$page->action}",
			];
			$urlRules[] = [
				'pattern' => "{$pageTranslation->slug}/<id>/<action>",
				'route' => "{$page->controller}/<action>",
			];
			$urlRules[] = [
				'pattern' => "{$pageTranslation->slug}/<action>",
				'route' => "{$page->controller}/<action>",
			];
		}

		return [
			'class' => 'yii\web\GroupUrlRule',
			'routePrefix' => $this->id,
			'prefix' => Inflector::slug(Yii::t($this->id, 'Payment')),
			'rules' => $urlRules,
		];
	}

	/**
	 * Builds URL Rules for DefaultController.
	 *
	 * @param \common\models\Page $page
	 * @param \common\models\PageTranslation $pageTranslation
	 * @return array
	 */
	protected function buildDefaultUrlRules($page, $pageTranslation)
	{
		$urlRules[] = [
			'pattern' => $pageTranslation->slug,
			'route' => "{$page->controller}/{$page->action}",
		];
		$urlRules[] = [
			'pattern' => "{$pageTranslation->slug}/" . Inflector::slug(Yii::t($this->id, 'Membership')) . '/<id>',
			'route' => "{$page->controller}/membership",
		];
		$urlRules[] = [
			'pattern' => "{$pageTranslation->slug}/" . Inflector::slug(Yii::t($this->id, 'Result')),
			'route' => "{$page->controller}/result",
		];

		return $urlRules;
	}
}

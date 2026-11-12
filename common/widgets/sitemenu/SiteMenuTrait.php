<?php

namespace common\widgets\sitemenu;

use common\models\MenuItem;
use Yii;

/**
 * Trait for SiteMenu widgets
 */
trait SiteMenuTrait
{
	/**
	 * @var bool|string Displays a tooltip on mouse over a menu item.
	 */
	public $displayTooltip = false;

	/**
	 * @var string The icon position inside the menu item.
	 */
	public $iconPosition = self::ICON_POSITION_LEFT;

	/**
	 * @var string The icon HTML template.
	 */
	public $iconTemplate = ' <span class="{icon}"></span> ';

	/**
	 * Builds a single menu item.
	 *
	 * @param MenuItem $menuItem
	 * @return array
	 */
	protected function buildItem($menuItem)
	{
		$item = [];
		$menuItemTranslation = $menuItem->getTranslation();
		$label = $menuItemTranslation->title;
		$url = $menuItemTranslation->url ?: '#';

		if ($menuItem->page_id) {
			$page = $menuItem->page;

			if (empty($label)) {
				$label = $page->translation->title;
			}
			if (strlen($url) > 1 && $url[0] === '#') {
				// Hash URL for this specific page
				$url = [$page->getRoute(), '#' => ltrim($url, '#')];
			} else if (empty($url) || $url === '#') {
				// Normal URL for this specific page
				$url = [$page->getRoute()];
			}

			// Add slug parameter if it is a dynamic page
			if ($page->controller === 'site' && $page->action === 'page') {
				// Use menu item slug or page slug (relative to domain, not parent menu)
				$slug = $menuItemTranslation->slug ?: $page->translation->slug;
				$url = ['/' . ltrim($slug, '/'), 'language' => Yii::$app->language];
			}
		}

		$item['label'] = $label;
		$item['url'] = $url;
		$item['options'] = $menuItem->getHtmlOptions('item');
		$item['linkOptions'] = $menuItem->getHtmlOptions('link');

		if (isset($menuItem->icon)) {
			$label = [
				strtr($this->iconTemplate, ['{icon}' => $menuItem->icon]),
				$item['label'],
			];
			if ($this->iconPosition === self::ICON_POSITION_RIGHT) {
				$label = array_reverse($label);
			}
			$item['label'] = implode(' ', $label);
		}

		if (isset($menuItem->target)) {
			$item['linkOptions']['target'] = $menuItem->target;
		}

		if ($this->displayTooltip !== false && $item['url'] !== '#') {
			$item['options']['title'] = strtr($this->displayTooltip, [
				'{0}' => trim(strip_tags($item['label'])),
			]);
		}

		return $item;
	}

	/**
	 * Builds the menu items.
	 *
	 * @param array $menuItems
	 * @return array
	 */
	public function buildItems($menuItems)
	{
		$items = [];

		foreach ($menuItems as $menuItem) {
			if (!isset($menuItem['model'])) {
				$items[] = $menuItem;
				continue;
			}
			if ($menuItem['model']->excluded) {
				if (!empty($menuItem['items'])) {
					$items = array_merge($items, $this->buildItems($menuItem['items']));
					continue;
				}
			} else {
				$item = $this->buildItem($menuItem['model']);
				if (empty($item['label']) || empty($item['url'])) {
					continue;
				}
				if (!empty($menuItem['items'])) {
					$item['items'] = $this->buildItems($menuItem['items']);
				}
				$items[] = $item;
			}
		}

		return $items;
	}
}

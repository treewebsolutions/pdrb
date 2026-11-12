<?php

namespace common\widgets\sitemenu;

use Yii;
use yii\widgets\Menu;

class SiteMenuBs5 extends Menu
{
	use SiteMenuTrait;

	const ICON_POSITION_LEFT = 'left';
	const ICON_POSITION_RIGHT = 'right';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->activeCssClass = 'current';

        $this->submenuTemplate = "\n<ul class='sub-menu'>\n{items}\n</ul>\n";

		$this->items = $this->buildItems($this->items);

        for ($i = 0; $i < count($this->items); $i++) {
            $classes[$i] = [];
            if ($this->items[$i]['items']) {
                $classes[$i][] = 'dropdown';
            }
            if (ltrim($this->items[$i]['url'][0], '/') == (\common\models\Page::findPageByPathInfo(Yii::$app->request->pathInfo)->translation->slug)) {
                $classes[$i][] = 'current';
            }
            $this->items[$i]['options']['class'] = implode(' ', $classes[$i]);
        }
	}
}
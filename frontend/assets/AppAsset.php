<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public $basePath = '@webroot';

	/**
	 * @inheritdoc
	 */
	public $baseUrl = '@web';

	/**
	 * @inheritdoc
	 */
	public $css = [
		'https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap',
		'https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap',
		'css/font-awesome-all.css',
		'css/flaticon.css',
		'css/owl.css',
		'css/bootstrap.css',
		'css/jquery.fancybox.min.css',
		'css/animate.css',
		'css/nice-select.css',
		'css/color.css',
		'css/style.css',
		'css/responsive.css',
		'css/custom.css',
	];

	/**
	 * @inheritdoc
	 */
	public $js = [
        'js/passive-events-fix.js',
        'js/owl.js',
        'js/wow.js',
        'js/validation.js',
        'js/jquery.fancybox.js',
        'js/appear.js',
        'js/scrollbar.js',
        'js/jquery.nice-select.min.js',
        'js/nav-tool.js',
        'js/script.js',
        'js/socialshare.js',
        'js/main-app.js',
	];

	/**
	 * @inheritdoc
	 */
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapPluginAsset',
		'frontend\assets\NpmAsset',
		'frontend\assets\BowerAsset',
	];
}

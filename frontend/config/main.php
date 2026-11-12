<?php
$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-frontend',
	'name' => 'LeVille Website',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'frontend\controllers',
	'bootstrap' => [
//        'assetsAutoCompress',
		'log',
		'frontend\components\ApplicationBootstrap',
        'article',
        'activity',
        'member',
        // The "payment" component (tws\payment\Payment) collides with the module ID,
        // so bootstrap the module explicitly to register its URL rules.
        function ($app) {
            if ($app->hasModule('payment')) {
                $app->getModule('payment')->bootstrap($app);
            }
        },
        'search',
        'sitemap',
	],
	'modules' => [
        'article' => [
            'class' => 'frontend\modules\article\Module',
        ],
        'activity' => [
            'class' => 'frontend\modules\activity\Module',
        ],
        'member' => [
            'class' => 'frontend\modules\member\Module',
        ],
        'payment' => [
            'class' => 'frontend\modules\payment\Module',
        ],
        'search' => [
            'class' => 'frontend\modules\search\Module',
        ],
        'sitemap' => [
            'class' => 'frontend\modules\sitemap\Module',
        ],
	],
	'components' => [
		'assetManager' => [
			'assetMap' => [
				'jquery.js' => '/js/jquery.js',
				'jquery.fancybox.min.js' => '/js/noop.js',
				'font-awesome.min.css' => '/css/empty.css',
				'jquery.fancybox.min.css' => '/css/empty.css',
				'animate.min.css' => '/css/empty.css',
				'bootstrap.css' => '/css/bootstrap.css',
			],
			'bundles' => [
				'yii\web\JqueryAsset' => [
					'sourcePath' => null,
					'js' => ['/js/jquery.js'],
				],
				'yii\bootstrap\BootstrapPluginAsset' => [
					'sourcePath' => null,
					'js' => [
						'/js/popper.min.js',
						'/js/bootstrap.min.js',
					],
					'depends' => ['yii\web\JqueryAsset'],
				],
				'yii\bootstrap\BootstrapAsset' => [
					'css' => [
						'/css/bootstrap.css',
					],
				],
			],
		],
		'assetsAutoCompress' => [
			'class'                         => '\skeeks\yii2\assetsAuto\AssetsAutoCompressComponent',
			'enabled'                        => true,
			'jsCompressFlaggedComments'     => true,        //Cut comments during processing js
			'cssFileBottom'                 => false,       //Moving down the page css files
			'jsFileCompressFlaggedComments' => true,        //Cut comments during processing js
		],
		'request' => [
			'baseUrl' => '',
			'csrfParam' => '_csrf',
			'enableCsrfValidation' => true,
			'enableCookieValidation' => true,
			'enableCsrfCookie' => false,
			'csrfCookie' => [
				'path' => '/',
				'httpOnly' => true,
			],
		],
		'user' => [
			'identityClass' => 'common\models\User',
			'enableAutoLogin' => true,
			'identityCookie' => [
				'path' => '/',
				'name' => '_identity',
				'httpOnly' => true,
			],
			'loginUrl' => ['/site/login'],
		],
		'session' => [
			'name' => 'PRINTSESSID',
			'useCookies' => true,
			'cookieParams' => [
				'path' => '/',
				'httpOnly' => true,
			],
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'urlManager' => [
			'class' => 'codemix\localeurls\UrlManager',
			'baseUrl' => '',
			'languages' => ['en' => 'en-US'],
			'enableLanguageDetection' => false,
			'enableDefaultLanguageUrlCode' => false,
			'enableLanguagePersistence' => false,
			'enableStrictParsing' => true,
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'normalizer' => [
				'class' => 'yii\web\UrlNormalizer',
				'action' => yii\web\UrlNormalizer::ACTION_REDIRECT_PERMANENT,
			],
			'rules' => [
				[
					'pattern' => '',
					'route' => '/site/index',
				],
				[
					'pattern' => '/rss',
					'route' => '/site/rss',
				],
				[
					'pattern' => '<action:(logout|accept-cookies|unsuscribe|ipn)>',
					'route' => '/site/<action>',
				],
			],
		],
	],
	'params' => $params,
];

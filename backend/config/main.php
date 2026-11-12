<?php
$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-backend',
	'name' => 'LeVille Administration',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'backend\controllers',
	'bootstrap' => [
		'log',
		'backend\components\ApplicationBootstrap',
		'setting-manager',
		'user-manager',
		'eventlog-manager',
		'website-manager',
		'nomenclature-manager',
		'marketing-manager',
        'import-manager',
        'export-manager',
	],
	'modules' => [
		'setting-manager' => [
			'class' => 'backend\modules\setting\Module',
		],
		'user-manager' => [
			'class' => 'backend\modules\user\Module',
		],
		'eventlog-manager' => [
			'class' => 'backend\modules\eventlog\Module',
		],
		'website-manager' => [
			'class' => 'backend\modules\website\Module',
		],
		'nomenclature-manager' => [
			'class' => 'backend\modules\nomenclature\Module',
		],
        'marketing-manager' => [
            'class' => 'backend\modules\marketing\Module',
        ],
        'import-manager' => [
            'class' => 'backend\modules\import\Module',
        ],
        'export-manager' => [
            'class' => 'backend\modules\export\Module',
        ],
	],
	'components' => [
		'request' => [
			'baseUrl' => '/admin',
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
			'loginUrl' => '/',
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
			'baseUrl' => '/admin',
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
					'pattern' => '/',
					'route' => '/site/index',
				],
				[
					'pattern' => '/profile',
					'route' => '/profile/index',
				],
				[
					'pattern' => '/<action:(login|logout|reset-password|search)>',
					'route' => '/site/<action>',
				],
				[
					'pattern' => '/<controller>/<id:(\d+|__\w+__)>/<action>',
					'route' => '/<controller>/<action>',
				],
				[
					'pattern' => '/<controller>/<action>',
					'route' => '/<controller>/<action>',
				],
			],
		],
		'eventLog' => [
			'class' => 'backend\modules\eventlog\components\EventLog',
			'model' => 'common\models\EventLog',
		],
		'translate' => [
			'class' => 'richweber\google\translate\Translation',
			'key' => '',
		],
		'i18n' => [
			'translations' => [
				'member' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@frontend/modules/member/messages',
					'forceTranslation' => true,
					'fileMap' => [
						'member' => 'i18n.php',
					],
				],
			],
		],
	],
	'params' => $params,
];

<?php

namespace frontend\controllers;

use common\models\Article;
use common\models\ActivateAccountForm;
use common\models\Language;
use common\models\LoginForm;
use common\models\MarketingRecipient;
use common\models\Membership;
use common\models\PaymentMetadata;
use common\models\Picture;
use common\models\ResetPasswordForm;
use common\models\ResetPasswordRequestForm;
use common\models\SignupForm;
use common\models\User;
use dosamigos\google\maps\LatLng;
use dosamigos\google\maps\Map;
use dosamigos\google\maps\overlays\InfoWindow;
use dosamigos\google\maps\overlays\Marker;
use frontend\models\AcceptCookiesForm;
use frontend\models\DonationForm;
use frontend\models\NewsletterForm;
use frontend\modules\payment\models\PaymentResult;
use tws\helpers\StringHelper;
use tws\helpers\Url;
use Yii;
use frontend\models\ContactForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Response;
use Zelenin\Feed;
use Zelenin\yii\extensions\Rss\RssView;

/**
 * Site controller
 */
class SiteController extends MainController
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'only' => ['logout'],
				'rules' => [
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ($action->id === 'ipn') {
			if (Yii::$app->request->isPost) {
				$this->enableCsrfValidation = false;
			}
		}
		return parent::beforeAction($action);
	}

	/**
	 * Displays homepage.
	 *
	 * @return mixed
	 * @throws \Throwable
	 */
	public function actionIndex()
	{
        $this->layout = 'home';

        $newsletterModel = new NewsletterForm();

        if ($newsletterModel->load(Yii::$app->request->post()) && $newsletterModel->validate()) {
            if ($newsletterModel->save()) {
                Yii::$app->session->setFlash('success', Yii::t('frontend', 'Thank you for subscribing to our newsletter. From now on you are closer to the news you will receive from us.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('common', 'This email have been already submitted to newsletter.'));
            }
            return $this->redirect(['index']);
        }


        $location = Yii::$app->settings->getCategory('general');
        $address = implode(', ', array_filter([
            $location['streetName'],
            $location['streetNumber'],
            $location['locality'],
            $location['zipCode'],
            $location['county'],
            $location['country'] ? \common\models\Country::findAllCountries()[$location['country']]->translation->name : null,
        ]));
        $latLng = new LatLng([
            'lat' => $location['latitude'],
            'lng' => $location['longitude'],
        ]);
        $map = new Map([
            'center' => $latLng,
            'zoom' => 17,
            'width' => '100%',
            'height' => '100%',
            'mapTypeControl' => false,
        ]);
        $marker = new Marker([
            'position' => $latLng,
            'title' => Yii::$app->name,
            'icon' => Yii::getAlias('@web/img/map-marker.png'),
        ]);

        if (!empty($address)) {
            $marker->attachInfoWindow(
                new InfoWindow([
                    'content' => $address,
                ])
            );
        }
        $map->addOverlay($marker);

        if (Yii::$app->request->isAjax) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        return $this->render('index', [
            'newsletterModel' => $newsletterModel,
            'address' => $address,
            'map' => $map,
		]);
	}

	/**
	 * Displays contact page.
	 *
	 * @return mixed
	 */
	public function actionContact()
	{
		$model = new ContactForm();
        $bodyParams = Yii::$app->request->post();

//		if (!Yii::$app->user->isGuest) {
//			$user = Yii::$app->user->identity;
//			$model->name = $user->getFullName();
//			$model->email = $user->email;
//			$model->phone = $user->phone;
//		}

		if ($model->load($bodyParams) && $model->validate()) {
			if ($model->sendEmail()) {
				Yii::$app->session->setFlash('success', Yii::t('frontend', 'Thank you for contacting us. We will respond to you as soon as possible.'));
			} else {
				Yii::$app->session->setFlash('error', Yii::t('common', 'There was an error sending your message.'));
			}
            $model->backUrl = $bodyParams['ContactForm']['backUrl'];
            if ($model->backUrl) {
                return $this->redirect($model->backUrl);
            }
			return $this->redirect(['contact']);
		}

		$contact = Yii::$app->settings->getCategory('contact');
		$address = implode(', ', array_filter([
			$contact['streetName'],
			$contact['streetNumber'],
			$contact['locality'],
			$contact['zipCode'],
			$contact['county'],
			$contact['country'] ? \common\models\Country::findAllCountries()[$contact['country']]->translation->name : null,
		]));
		$latLng = new LatLng([
			'lat' => $contact['latitude'],
			'lng' => $contact['longitude'],
		]);
		$map = new Map([
			'center' => $latLng,
			'zoom' => 17,
			'width' => '100%',
			'height' => '100%',
			'mapTypeControl' => false,
		]);
		$marker = new Marker([
			'position' => $latLng,
			'title' => Yii::$app->name,
			'icon' => Yii::getAlias('@web/img/map-marker.png'),
		]);

		if (!empty($address)) {
            $marker->attachInfoWindow(
                new InfoWindow([
                    'content' => $address,
                ])
            );
        }
		$map->addOverlay($marker);

		return $this->render('contact', [
			'model' => $model,
			'contact' => $contact,
			'address' => $address,
			'map' => $map,
		]);
	}

	/**
	 * Displays the branches page: each branch with its details and map.
	 *
	 * @return mixed
	 */
	public function actionBranch()
	{
		return $this->render('branch', [
			'branches' => \common\models\Branch::findAllBranches(),
		]);
	}

	/**
	 * Displays membership page and handles form submissions.
	 *
	 * @param string|null $type
	 * @return mixed
	 */
	public function actionMembership($type = null)
	{
		$model = new \frontend\models\MembershipForm();
		
		// Set default country to Belgium
		$model->country = 'BE';
		
		// Map translated type values back to English keys
		// This allows URLs like /membership/voluntar (Romanian) to work
		$typeReverseMap = [];
		$englishTypeKeys = ['Founder', 'Contributor', 'Member', 'Volunteer'];
		$originalLanguage = Yii::$app->language;
		foreach (Language::findAllLanguages() as $language) {
			Yii::$app->language = $language->language_id;
			foreach ($englishTypeKeys as $englishKey) {
				$translatedValue = strtolower(Yii::t('member', $englishKey));
				$typeReverseMap[$translatedValue] = strtolower($englishKey);
				// Also add slug version (with dashes)
				$translatedSlug = strtolower(str_replace(' ', '-', Yii::t('member', $englishKey)));
				$typeReverseMap[$translatedSlug] = strtolower($englishKey);
			}
		}
		Yii::$app->language = $originalLanguage;
		
		// Also add English keys directly
		foreach ($englishTypeKeys as $englishKey) {
			$typeReverseMap[strtolower($englishKey)] = strtolower($englishKey);
		}
		
		// Add custom slug mappings (e.g., membru-cotizant -> contributor)
		$customSlugMap = [
			'membru-cotizant' => 'contributor',
			'membru-contribuitor' => 'contributor',
			'member-contributor' => 'contributor',
		];
		foreach ($customSlugMap as $slug => $key) {
			$typeReverseMap[strtolower($slug)] = strtolower($key);
		}
		
		// Determine the English type key from URL parameter
		$detectedTypeKey = null;
		$customMessage = null;
		if (!empty($type)) {
			$typeLower = strtolower($type);
			if (isset($typeReverseMap[$typeLower])) {
				$detectedTypeKey = $typeReverseMap[$typeLower];
			}
			
			// Check if it's a custom slug that needs a specific message
			// For example: membru-cotizant -> "Contributor" (which translates to "Membru Cotizant" in Romanian)
			if (in_array($typeLower, array_keys($customSlugMap))) {
				// Generate custom message from the slug for other custom slugs
				$customMessage = [];
				$originalLanguage = Yii::$app->language;
				foreach (Language::findAllLanguages() as $language) {
					Yii::$app->language = $language->language_id;
					// Convert slug to readable format and translate
					$slugParts = explode('-', $typeLower);
					$translatedParts = [];
					foreach ($slugParts as $part) {
						// Try to find translation for each part
						$found = false;
						foreach ($englishTypeKeys as $englishKey) {
							$translated = strtolower(Yii::t('member', $englishKey));
							if ($translated === $part || strpos($translated, $part) === 0) {
								$translatedParts[] = Yii::t('member', $englishKey);
								$found = true;
								break;
							}
						}
						if (!$found) {
							// Use capitalized version if no translation found
							$translatedParts[] = ucfirst($part);
						}
					}
					$customMessage[$language->language_id] = implode(' ', $translatedParts);
				}
				Yii::$app->language = $originalLanguage;
			}
		}
		
		// Set member type if provided via URL parameter
		// Only set type for volunteer, for other types or when no type is provided, leave it unset
		if (!empty($detectedTypeKey) && $detectedTypeKey === 'volunteer') {
			$model->type = \common\models\Member::TYPE_VOLUNTEER;
		}
		// For other types (founder, contributor, member) or when no type parameter, 
		// leave type unset so user must select it
		
		// Generate multilingual message for type - use translated type name
		$typeMessage = [];
		if (!empty($customMessage)) {
			// Use custom message if available (for composite slugs like membru-cotizant)
			$typeMessage = $customMessage;
		} elseif (!empty($detectedTypeKey)) {
			// Map English type key to translation key
			$typeTranslationMap = [
				'founder' => 'Founder',
				'contributor' => 'Contributor',
				'member' => 'Member',
				'volunteer' => 'Volunteer',
			];
			if (isset($typeTranslationMap[$detectedTypeKey])) {
				$translationKey = $typeTranslationMap[$detectedTypeKey];
				$originalLanguage = Yii::$app->language;
				foreach (Language::findAllLanguages() as $language) {
					// Temporarily set language to get translation
					Yii::$app->language = $language->language_id;
					$typeMessage[$language->language_id] = Yii::t('member', $translationKey);
				}
				// Restore original language
				Yii::$app->language = $originalLanguage;
			}
		}
		
		// Set role with type message if available, otherwise use default
		if (!empty($typeMessage)) {
			$model->role = $typeMessage;
		} else {
			$model->role[Yii::$app->language] = Yii::t('frontend', 'Membership Application');
		}

		// Filter type labels based on URL parameter
		if (!empty($detectedTypeKey) && $detectedTypeKey === 'volunteer') {
			$filteredTypeLabels = [
				\common\models\Member::TYPE_VOLUNTEER => Yii::t('member', 'Volunteer'),
			];
		} else {
			$filteredTypeLabels = \common\models\Member::getMembershipApplicationTypeLabels();
			if (\frontend\models\MembershipForm::isCurrentUserSuperAdmin()) {
				$filteredTypeLabels += \common\models\Member::getSuperadminTestMembershipApplicationLabels();
			}
		}

		// Handle form submission
		if ($model->load(Yii::$app->request->post())) {
			if ($model->validate()) {
				if ($model->save()) {
					// Paid: redirect straight to Stripe Checkout (card or Bancontact only).
					if ($model->getRequiresPayment()) {
						if ($checkoutUrl = $model->createStripeCheckoutUrl()) {
							return $this->redirect($checkoutUrl);
						}
						Yii::$app->session->setFlash('error', Yii::t('frontend', 'Cannot process your request. Please try again or contact us if the problem persists.'));
					} else {
						Yii::$app->session->setFlash('success', Yii::t('frontend', 'Thank you for your membership application. We will review it and contact you soon.'));
						return $this->refresh();
					}
				} else {
					Yii::$app->session->setFlash('error', Yii::t('common', 'There was an error processing your application. Please try again.'));
				}
			} else {
				// Show validation errors
				foreach ($model->errors as $field => $errors) {
					foreach ($errors as $error) {
						Yii::$app->session->addFlash('error', $field . ': ' . $error);
					}
				}
			}
		}

		$contact = Yii::$app->settings->getCategory('contact');
		$address = implode(', ', array_filter([
			$contact['streetName'],
			$contact['streetNumber'],
			$contact['locality'],
			$contact['zipCode'],
			$contact['county'],
			$contact['country'] ? \common\models\Country::findAllCountries()[$contact['country']]->translation->name : null,
		]));

		// Set page title and meta tags to membership page title + type value if parameter exists (after registerMetaData has run)
		$typeTitle = null;
		$pageTitle = \common\models\Page::findPageByRoute(['site/membership'])->translation->title;
		if ($currentPage = $this->getCurrentPage()) {
			$pageTitle = $currentPage->getTranslation()->title;
		}
		
		if (!empty($detectedTypeKey)) {
			$typeTranslationMap = [
				'founder' => 'Founder',
				'contributor' => 'Contributor',
				'member' => 'Member',
				'volunteer' => 'Volunteer',
			];
			if (isset($typeTranslationMap[$detectedTypeKey])) {
				$typeTitle = Yii::t('member', $typeTranslationMap[$detectedTypeKey]);
				$view = $this->getView();
				// Combine page title with type value
				if ($pageTitle) {
					$combinedTitle = $pageTitle . ': ' . $typeTitle;
				} else {
					$combinedTitle = $typeTitle;
				}
				$view->title = $combinedTitle;
				// Update Open Graph title meta tag
				$view->registerMetaTag(['property' => 'og:title', 'content' => $combinedTitle], 'og:title');
			}
		}

		return $this->render('membership', [
			'model' => $model,
			'contact' => $contact,
			'address' => $address,
			'type' => $type,
			'typeMessage' => $typeMessage,
			'typeLabels' => $filteredTypeLabels,
			'typeTitle' => $typeTitle,
		]);
	}

	/**
	 * Displays terms and conditions page.
	 *
	 * @return mixed
	 */
	public function actionTermsAndConditions()
	{
		return $this->render('terms-and-conditions');
	}

	/**
	 * Displays privacy policy page.
	 *
	 * @return mixed
	 */
	public function actionPrivacyPolicy()
	{
        return $this->render('privacy-policy');
	}

	/**
	 * Displays privacy policy page.
	 *
	 * @return mixed
	 */
	public function actionCookiePolicy()
	{
        return $this->render('cookie-policy');
	}

	/**
	 * Logs in a user.
	 *
	 * @return mixed
	 */
	public function actionLogin()
	{
		if (!Yii::$app->user->isGuest) {
			Yii::$app->session->setFlash('info', Yii::t('common', 'You are already authenticated.'));
			return $this->goHome();
		}

		$model = new LoginForm();

		if ($model->load(Yii::$app->request->post())) {
			if ($model->login()) {
				return $this->goBack();
			}
			Yii::$app->session->setFlash('error', Yii::t('common', 'The provided credentials are invalid.'));
		}

		return $this->render('login', [
			'model' => $model,
		]);
	}

	/**
	 * Logs out the current user.
	 *
	 * @return mixed
	 */
	public function actionLogout()
	{
		Yii::$app->user->logout();

		return $this->goHome();
	}

	/**
	 * Signs user up.
	 *
	 * @return mixed
	 */
	public function actionSignup()
	{
		if (!Yii::$app->user->isGuest) {
			Yii::$app->session->setFlash('info', Yii::t('common', 'You already have an account.'));
			return $this->goHome();
		}

		$model = new SignupForm();

		if ($model->load(Yii::$app->request->post()) && $model->signup()) {
			Yii::$app->trigger('user.afterSignup', new \yii\base\Event(['sender' => $model->getUser()]));
			if (Yii::$app->settings->get('userAccountActivation') == User::ACCOUNT_ACTIVATION_CONFIRMATION) {
				if ($model->sendActivationEmail()) {
					Yii::$app->session->setFlash('success', Yii::t('common', 'Please check your email to activate your account.'));
				} else {
					Yii::$app->session->setFlash('error', Yii::t('common', 'Cannot send the activation message.'));
				}
				return $this->redirect(['/site/index']);
			} else {
				Yii::$app->session->setFlash('success', Yii::t('common', 'Your account was successfully created.'));
				if (Yii::$app->getUser()->login($model->getUser(), (int) Yii::$app->settings->get('userLoginDuration'))) {
					return $this->goHome();
				}
				return $this->redirect(['/site/index']);
			}
		}

		return $this->render('signup', [
			'model' => $model,
		]);
	}

	/**
	 * Activates user account.
	 *
	 * @param string|int|null $token
	 * @return mixed
	 */
	public function actionActivate($token = null)
	{
		if (!Yii::$app->user->isGuest) {
			Yii::$app->session->setFlash('info', Yii::t('common', 'Your account is already active.'));
			return $this->goHome();
		}

		$model = new ActivateAccountForm();
		$model->token = $token;

		if (($model->load(Yii::$app->request->post()) || !empty($token)) && $model->validate()) {
			if ($model->activate()) {
				Yii::$app->trigger('user.afterAccountActivation', new \yii\base\Event(['sender' => $model->getUser()]));
				Yii::$app->session->setFlash('success', Yii::t('common', 'Your account has been activated.'));
				if (Yii::$app->getUser()->login($model->getUser(), (int) Yii::$app->settings->get('userLoginDuration'))) {
					return $this->redirect(['/account/default/index']);
				}
				return $this->redirect(['/site/login']);
			}
			Yii::$app->session->setFlash('error', Yii::t('common', 'Cannot activate the account due to invalid data provided.'));
		}

		return $this->render('activate', [
			'model' => $model,
		]);
	}

	/**
	 * Resets password.
	 *
	 * @param null|string $token
	 * @return mixed
	 */
	public function actionResetPassword($token = null)
	{
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}

		$resetPasswordRequestModel = new ResetPasswordRequestForm();
		$resetPasswordModel = new ResetPasswordForm();
		$resetPasswordModel->token = $token;
		$resetPasswordModel->setScenario(ResetPasswordForm::SCENARIO_TOKEN);

		if ($resetPasswordRequestModel->load(Yii::$app->request->post()) && $resetPasswordRequestModel->validate() && $resetPasswordRequestModel->sendRequest()) {
			Yii::$app->session->setFlash('success', Yii::t('common', 'You have requested a password reset. Please check the message that was sent to {0}.', [$resetPasswordRequestModel->username]));
			return $this->redirect(['reset-password']);
		} else {
			$resetPasswordModel->load(Yii::$app->request->post());
			// Switch to enter password scenario if the provided token is valid
			if (!empty($resetPasswordModel->token) && $resetPasswordModel->getUser()) {
				$resetPasswordModel->setScenario(ResetPasswordForm::SCENARIO_PASSWORD);
				if ($resetPasswordModel->token != $token) {
					return $this->redirect(['reset-password', 'token' => $resetPasswordModel->token]);
				}
			}
			if ($resetPasswordModel->load(Yii::$app->request->post()) && $resetPasswordModel->validate() && $resetPasswordModel->resetPassword()) {
				Yii::$app->session->setFlash('success', Yii::t('common', 'New password saved.'));
				if ($user = $resetPasswordModel->getUser()) {
					$user = is_array($user) ? reset($user) : $user;
					if (Yii::$app->getUser()->login($user, (int) Yii::$app->settings->get('userLoginDuration'))) {
						return $this->goHome();
					}
				}
			}
		}

		return $this->render('reset-password', [
			'resetPasswordRequestModel' => $resetPasswordRequestModel,
			'resetPasswordModel' => $resetPasswordModel,
		]);
	}

    /**
     * Displays about-us page.
     *
     * @return mixed
     */
    public function actionAboutUs()
    {
    	return $this->render('about-us');
    }

	/**
	 * Displays donate page and handles donation checkout (Stripe).
	 *
	 * @return mixed
	 */
	public function actionDonate()
	{
		$model = new DonationForm();

		if ($model->load(Yii::$app->request->post())) {
			if ($model->validate() && $model->validateAntiSpam()) {
				if ($checkoutUrl = $model->createStripeCheckoutUrl()) {
					return $this->redirect($checkoutUrl);
				}
				Yii::$app->session->setFlash('error', Yii::t('frontend', 'Cannot process your request. Please try again or contact us if the problem persists.'));
			} else {
				foreach ($model->errors as $field => $errors) {
					foreach ($errors as $error) {
						Yii::$app->session->addFlash('error', $field . ': ' . $error);
					}
				}
			}
		}

		return $this->render('donate', [
			'model' => $model,
		]);
	}

    /**
     * Displays gallery page.
     *
     * @return mixed
     */
    public function actionGallery()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Picture::providePictures(Picture::TYPE_GENERAL),
            'pagination' => [
                'defaultPageSize' => 24,
            ],
        ]);

        return $this->render('gallery', [
            'dataProvider' => $dataProvider,
        ]);
    }

	/**
	 * Site accept cookies action.
	 *
	 * @return mixed
	 */
	public function actionAcceptCookies()
	{
		$model = new AcceptCookiesForm();
		$model->acceptCookies = Yii::$app->request->post('acceptCookies');
		$model->backUrl = Yii::$app->request->post('backUrl');
		if ($model->setCookies()) {
			return $this->redirect($model->backUrl);
		}
        return $this->redirect(Url::base(true) . '/' . (Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' :  mb_substr(Yii::$app->language, 0, 2)));
	}

	/**
	 * Displays any dynamic page.
	 *
	 * @return mixed
	 */
	public function actionPage()
	{
		return $this->render('page');
	}

	/**
	 * Displays rss page.
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function actionRss()
	{
		$response = Yii::$app->getResponse();
		$response->format = Response::FORMAT_RAW;

		$headers = $response->getHeaders();
		$headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

		$dataProvider = new ActiveDataProvider([
			'query' => Article::provideRssArticles(),
			'pagination' => [
				'pageSize' => 10,
			],
		]);

		return RssView::widget([
			'dataProvider' => $dataProvider,
			'channel' => [
				'title' => function ($widget, Feed $feed) {
					$feed->addChannelTitle(Yii::$app->name);
				},
				'link' => Url::to(['/site/index'], true),
				'description' => Yii::t('common', 'Articles'),
				'language' => function ($widget, Feed $feed) {
					return Yii::$app->language;
				},
				'image'=> function ($widget, Feed $feed) {
					if (!($logoUrl = Url::to(Yii::$app->settings->get('appLogo')))) {
						$logoUrl = Url::to('@web/img/logo.png', true);
					}
					$feed->addChannelImage($logoUrl, Url::home(true), 144, 36, Yii::$app->name);
				},
			],
			'items' => [
				'title' => function (Article $model, $widget, Feed $feed) {
					return $model->translation->title;
				},
				'description' => function (Article $model, $widget, Feed $feed) {
					return StringHelper::truncateWords($model->translation->description, 50);
				},
				'link' => function (Article $model, $widget, Feed $feed) {
					return Url::to(['/article/default/view', 'slug' => $model->translation->slug], true);
				},
				'guid' => function (Article $model, $widget, Feed $feed) {
					return Url::to(['/article/default/view', 'slug' => $model->translation->slug], true);
				},
				'pubDate' => function (Article $model, $widget, Feed $feed) {
					return (new \DateTime($model->created_at))->format(DATE_RSS);
				},
			],
		]);
	}

	public function actionUnsuscribe()
	{
		$model = MarketingRecipient::findOne(['id' => Yii::$app->security->unmaskToken(Yii::$app->request->get('id')), 'status' => 1]);
		if (!$model) {
			Yii::$app->session->setFlash('error', Yii::t('frontend', 'No active subscriber to newsletter for these contact details was found.'));
		} else {
			$model->detachBehavior('BlameableBehavior');
			$model->status = MarketingRecipient::STATUS_INACTIVE;
			if ($model->save()) {
				Yii::$app->session->setFlash('success', Yii::t('frontend', 'You have successfully unsuscribed from newsletter.'));
			} else {
				Yii::$app->session->setFlash('error', Yii::t('frontend', 'No active subscriber to newsletter for these contact details was found.'));
			}
		}
		return $this->redirect(Url::base(true) . '/' . (Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' :  mb_substr(Yii::$app->language, 0, 2)));
	}

	/**
	 * IPN endpoint for the Stripe webhook (membership payments).
	 *
	 * @return mixed
	 */
	public function actionIpn()
	{
		if (!Yii::$app->request->isPost) {
			return $this->redirect(['index']);
		}

		$paymentSettings = Yii::$app->settings->getCategory('payment');
		$paymentProcessors = (array) ($paymentSettings['paymentProcessors'][PaymentMetadata::PAYMENT_METHOD_CARD] ?? []);
		$model = new PaymentResult();

		if (!array_key_exists(PaymentMetadata::PAYMENT_METHOD_CARD, $paymentProcessors)) {
			$model->addError('', Yii::t('frontend', 'Payment response is not valid.'));
			http_response_code(400);
			return;
		}

		$endpointSecret = $paymentSettings['stripeWebhookIPNKey'];
		$payload = @file_get_contents('php://input');
		$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload, $sigHeader, $endpointSecret
			);
		} catch (\UnexpectedValueException $e) {
			http_response_code(400);
			exit();
		} catch (\Stripe\Exception\SignatureVerificationException $e) {
			http_response_code(400);
			exit();
		}

		switch ($event->type) {
			case 'checkout.session.async_payment_failed':
			case 'checkout.session.expired':
				$model->addError('', Yii::t('frontend', 'Payment failed.'));
				break;

			case 'checkout.session.async_payment_succeeded':
			case 'checkout.session.completed':
				$session = $event->data->object;
				if (empty($session) || ($session['payment_status'] ?? null) !== 'paid') {
					break;
				}

				$sessionMeta = !empty($session['metadata'])
					? (json_decode(json_encode($session['metadata']), true) ?: [])
					: [];
				if (($sessionMeta['donation'] ?? '') === '1') {
					break;
				}

				$membershipId = $sessionMeta['membership_id'] ?? $session['metadata']['membership_id'] ?? null;
				if (empty($membershipId)) {
					break;
				}

				$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
				$stripe = new \Stripe\StripeClient($payment->privateKey);

				$attributes = [
					'membership_id' => $membershipId,
					'payer_id' => $session['customer'] ?? null,
				];

				if (!empty($session['payment_intent'])) {
					$paymentIntent = $stripe->paymentIntents->retrieve(
						$session['payment_intent'],
						[]
					);
					if ($paymentIntent) {
						$attributes['payment_id'] = $paymentIntent['id'] ?? null;
						$charges = $paymentIntent['charges']['data'] ?? [];
						if (!empty($charges[0]['id'])) {
							$attributes['transaction_id'] = $charges[0]['id'];
						}
					}
				}

				if ($membership = Membership::findOne(['id' => $membershipId, 'deleted' => Membership::NO])) {
					if (empty($membership->paymentMetadata)) {
						$paymentMetadata = new PaymentMetadata();
						$paymentMetadata->detachBehavior('BlameableBehavior');
						$paymentMetadata->membership_id = $membership->id;
						$paymentMetadata->payment_processor = PaymentMetadata::PAYMENT_PROCESSOR_STRIPE;
						$paymentMetadata->payment_method = PaymentMetadata::PAYMENT_METHOD_CARD;
						$paymentMetadata->recurring_payment = PaymentMetadata::NO;
						$paymentMetadata->ip_address = $session['metadata']['ip'] ?? null;
						$paymentMetadata->status = PaymentMetadata::STATUS_ACTIVE;
						$paymentMetadata->save(false);
					}
				}

				$model->load($attributes, '');
				$model->save();
				break;

			case 'payment_intent.succeeded':
				$piObj = $event->data->object;
				$membershipId = $piObj->metadata['membership_id'] ?? null;
				if (empty($membershipId)) {
					break;
				}

				$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
				$stripe = new \Stripe\StripeClient($payment->privateKey);
				$fullPi = $stripe->paymentIntents->retrieve($piObj->id);

				if ($fullPi->status !== 'succeeded') {
					break;
				}

				$attributes = [
					'membership_id' => $membershipId,
					'payer_id' => $fullPi->customer ?? null,
					'payment_id' => $fullPi->id,
				];
				$transactionId = $fullPi->latest_charge ?? null;
				if (!$transactionId && !empty($fullPi->charges->data[0]->id)) {
					$transactionId = $fullPi->charges->data[0]->id;
				}
				if ($transactionId) {
					$attributes['transaction_id'] = $transactionId;
				}

				if ($membership = Membership::findOne(['id' => $membershipId, 'deleted' => Membership::NO])) {
					if (empty($membership->paymentMetadata)) {
						$paymentMetadata = new PaymentMetadata();
						$paymentMetadata->detachBehavior('BlameableBehavior');
						$paymentMetadata->membership_id = $membership->id;
						$paymentMetadata->payment_processor = PaymentMetadata::PAYMENT_PROCESSOR_STRIPE;
						$paymentMetadata->payment_method = PaymentMetadata::PAYMENT_METHOD_BANCONTACT;
						$paymentMetadata->recurring_payment = PaymentMetadata::NO;
						$paymentMetadata->ip_address = $fullPi->metadata['ip'] ?? null;
						$paymentMetadata->status = PaymentMetadata::STATUS_ACTIVE;
						$paymentMetadata->save(false);
					}
				}

				$model->load($attributes, '');
				$model->save();
				break;

			default:
				break;
		}

		http_response_code(200);
	}
}

<?php

namespace frontend\modules\payment\controllers;

use common\models\PaymentMetadata;
use frontend\controllers\MainController;
use frontend\modules\payment\models\PaymentMembershipForm;
use frontend\modules\payment\models\PaymentRequest;
use frontend\modules\payment\models\PaymentResult;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class DefaultController extends MainController
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index', 'membership'],
						'roles' => ['@'],
					],
					[
						// Guests are allowed: a member registering for the first time may not be
						// authenticated yet when redirected back from the Stripe Checkout.
						'allow' => true,
						'actions' => ['result'],
						'roles' => ['?', '@'],
						'verbs' => ['GET', 'POST'],
					],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ($action->id === 'result') {
			if (Yii::$app->request->isPost) {
				$this->enableCsrfValidation = false;
			}
		}

		return parent::beforeAction($action);
	}

	/**
	 * Displays index page.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		return $this->redirect(['/site/index']);
	}

	/**
	 * Payment page for Membership model.
	 *
	 * @param int|string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the Membership model was not found.
	 */
	public function actionMembership($id)
	{
		$model = new PaymentMembershipForm();
		$model->membership_id = Yii::$app->security->unmaskToken((string) $id);

		if (!$model->getMembership()) {
			throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
		}

		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			if ($model->save()) {
				if ($model->payment_method == PaymentMetadata::PAYMENT_METHOD_CARD) {
					$paymentRequestModel = new PaymentRequest();
					$paymentRequestModel->type = PaymentMetadata::PAYMENT_TYPE_MEMBERSHIP;
					$paymentRequestModel->membership_id = $model->getMembership()->id;
					$paymentRequestModel->payment_processor = $model->payment_processor;
					$paymentRequestModel->validate();
					return $this->render('request', [
						'model' => $paymentRequestModel,
					]);
				} elseif ($model->payment_method == PaymentMetadata::PAYMENT_METHOD_BANK_TRANSFER) {
					Yii::$app->session->setFlash('success', Yii::t('frontend', 'Your membership was successfully registered and it will be activated as soon as the payment is completed.'));
					return $this->redirect(['/site/index']);
				}
			}
			Yii::$app->session->setFlash('error', Yii::t('frontend', 'Cannot process your request. Please try again or contact us if the problem persists.'));
		}

		return $this->render('membership', [
			'model' => $model,
		]);
	}

	/**
	 * Displays payment result page.
	 *
	 * @return mixed
	 */
	public function actionResult()
	{
		if (!($params = Yii::$app->request->post())) {
			$params = Yii::$app->request->get();
		}

		if (!empty($params['cancel'])) {
			return $this->redirect(['/site/index']);
		}

		$model = new PaymentResult();
		$handled = false;

		if (!empty($params['session_id'])) {
			$handled = true;
			$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
			$stripe = new \Stripe\StripeClient(
				$payment->privateKey
			);
			$session = $stripe->checkout->sessions->retrieve(
				$params['session_id'],
				[]
			);

			$sessionMeta = !empty($session['metadata'])
				? (json_decode(json_encode($session['metadata']), true) ?: [])
				: [];

			if (($sessionMeta['donation'] ?? '') === '1') {
				$paymentStatus = $session['payment_status'] ?? null;
				if (!empty($session) && $paymentStatus === 'paid') {
					$amountEur = isset($sessionMeta['amount_eur']) ? (float) $sessionMeta['amount_eur'] : null;

					return $this->render('result', [
						'model' => new PaymentResult(),
						'donationContext' => [
							'success' => true,
							'amount' => $amountEur,
						],
					]);
				}
				$model->addError('', Yii::t('frontend', 'Payment failed.'));

				return $this->render('result', [
					'model' => $model,
					'donationContext' => [
						'success' => false,
					],
				]);
			}

			if (!empty($session) && $session['payment_status'] == 'paid') {
				$attributes = [
					'membership_id' => $session['metadata']['membership_id'] ?? null,
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

				$model->load($attributes, '');
				$model->save();
			} else {
				$model->addError('', Yii::t('frontend', 'Payment failed.'));
			}
		}

		if (!$handled && !empty($params['payment_intent'])) {
			$handled = true;
			$payment = Yii::$app->payment->via(PaymentMetadata::PAYMENT_PROCESSOR_STRIPE);
			$stripe = new \Stripe\StripeClient($payment->privateKey);
			try {
				$pi = $stripe->paymentIntents->retrieve($params['payment_intent']);
			} catch (\Throwable $e) {
				$model->addError('', Yii::t('frontend', 'Payment response is not valid.'));
				$pi = null;
			}

			if ($pi) {
				if ($pi->status === 'succeeded') {
					$attributes = [
						'membership_id' => $pi->metadata['membership_id'] ?? null,
						'payer_id' => $pi->customer ?? null,
						'payment_id' => $pi->id,
					];
					$transactionId = $pi->latest_charge ?? null;
					if (!$transactionId && !empty($pi->charges->data[0]->id)) {
						$transactionId = $pi->charges->data[0]->id;
					}
					if ($transactionId) {
						$attributes['transaction_id'] = $transactionId;
					}
					$model->load($attributes, '');
					$model->save();
				} elseif ($pi->status === 'processing') {
					$model->addError('', Yii::t('frontend', 'Your payment is being processed. You will receive confirmation shortly.'));
				} else {
					$model->addError('', Yii::t('frontend', 'Payment failed.'));
				}
			}
		}

		if (!$handled) {
			$model->addError('', Yii::t('frontend', 'Payment response is not valid.'));
		}

		return $this->render('result', [
			'model' => $model,
		]);
	}
}

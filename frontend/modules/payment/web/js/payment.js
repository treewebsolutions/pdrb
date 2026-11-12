/**
 * Payment app.
 */
(function ($, window, document, undefined) {
	this.paymentApp = {
		/**
		 * @var string The form ID.
		 */
		formId: '#payment-form',

		/**
		 * Initialization.
		 */
		init: function () {
			this.cacheElements();
			this.bindEvents();
		},

		/**
		 * Caches DOM elements.
		 */
		cacheElements: function () {
			this.$window = $(window);
			this.$document = $(document);
			this.$html = $('html');
			this.$body = this.$html.children('body');
			this.$form = $(this.formId);
			this.$paymentMethodField = this.$form.find('[name*="payment_method"]');
			this.$paymentProcessorField = this.$form.find('[name*="payment_processor"]');
			this.$tokenField = this.$form.find('[name*="token"]');
			this.$submitBtn = this.$form.find('#btn-submit-payment');
		},

		/**
		 * Binds events.
		 */
		bindEvents: function () {
			this.$window.on('popstate.paymentApp', this._onWindowPopState.bind(this));
			this.$document.on('change.paymentApp', '[name*="payment_method"]', this._onPaymentMethodChange.bind(this));
			this.$document.on('submit.paymentApp', this.formId, this._onFormSubmit.bind(this));
		},

		/**
		 * Handles window popstate event.
		 *
		 * @param e
		 * @private
		 */
		_onWindowPopState: function (e) {
			if (this._stripeHandler) {
				this._stripeHandler.close();
			}
		},

		/**
		 * Handles payment method change event.
		 *
		 * @param e
		 * @private
		 */
		_onPaymentMethodChange: function (e) {
			var $target = $(e.currentTarget),
				targetData = $target.data(),
				$pmHints = this.$form.find('#pm-hints').children(),
				$pmProcessorContainer = this.$form.find('#pp-container'),
				$submitBtnLabels = this.$form.find('button[type="submit"]').children();

			$pmHints.addClass('hidden').filter('[id^="pm-hint-' + $target.val() + '"]').removeClass('hidden');
			$pmProcessorContainer.toggleClass('hidden', !targetData.requiresPaymentProcessor);

			$submitBtnLabels.addClass('hidden');
			if (targetData.requiresPaymentProcessor) {
				$submitBtnLabels.filter('.btn-submit-label-payment').removeClass('hidden');
			} else {
				$submitBtnLabels.filter('.btn-submit-label-submit').removeClass('hidden');
			}
		},

		/**
		 * Handles form submit event.
		 *
		 * @param e
		 * @private
		 */
		_onFormSubmit: function (e) {
			if (this.$form.data('isDefaultPrevented') !== false) {
				if (this.getPaymentMethodField().data('requiresPaymentProcessor') === true) {
					var paymentProcessor = this.getPaymentProcessorField().val();
					if (typeof this['paymentVia' + paymentProcessor] === 'function') {
						this['paymentVia' + paymentProcessor].call(this);
					}
					e.preventDefault();
				}
			}
		},

		/**
		 * Gets the app name.
		 *
		 * @return {string}
		 */
		getAppName: function () {
			if (typeof this._appName === 'undefined') {
				this._appName = this.$html.children('head').find('meta[property="og:site_name"]').attr('content');
			}
			return this._appName;
		},

		/**
		 * Gets the app logo.
		 *
		 * @return {string}
		 */
		getAppLogo: function () {
			if (typeof this._appLogo === 'undefined') {
				this._appLogo = this.$html.children('head').find('[property="og:image"]').attr('content');
			}
			return this._appLogo;
		},

		/**
		 * Gets the selected payment method field.
		 *
		 * @return {*}
		 */
		getPaymentMethodField: function () {
			if (this.$paymentMethodField.length > 1) {
				return this.$paymentMethodField.filter(':checked');
			}
			return this.$paymentMethodField;
		},

		/**
		 * Gets the selected payment processor field.
		 *
		 * @return {*}
		 */
		getPaymentProcessorField: function () {
			if (this.$paymentProcessorField.length > 1) {
				return this.$paymentProcessorField.filter(':checked');
			}
			return this.$paymentProcessorField;
		},

		/**
		 * Sets the amount to the submit button label.
		 *
		 * @param {String} amount
		 */
		setSubmitButtonAmount: function (amount) {
			var $paymentLabelTag = this.$submitBtn.find('.btn-submit-label-payment'),
				$amountTag = $paymentLabelTag.find('.btn-submit-amount');

			if (!$amountTag.length) {
				$amountTag = $('<span/>', {'class': 'btn-submit-amount'}).appendTo($paymentLabelTag);
			}

			$amountTag.text(amount);
		},

		/**
		 * Make payment using Stripe.
		 */
		paymentVia1: function () {
			this.$form.data('isDefaultPrevented', false).submit();
			this.$form.find('button[type="submit"]').prop('disabled', false);
		},

		/**
		 * Make payment using PayPal.
		 */
		paymentVia2: function () {
			alert('PayPal payment is not available right now.');
			this.$form.find('button[type="submit"]').prop('disabled', false);
		}
	};
})(jQuery, window, document);


/**
 * Document ready
 */
$(document).ready(function () {
	paymentApp.init();
});

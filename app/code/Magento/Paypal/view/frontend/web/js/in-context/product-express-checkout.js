/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'uiComponent',
    'Magento_Paypal/js/in-context/express-checkout-wrapper',
    'Magento_Customer/js/customer-data'
], function (_, $, Component, Wrapper, customerData) {
    'use strict';

    return Component.extend(Wrapper).extend({
        defaults: {
            productFormSelector: '#product_addtocart_form',
            declinePayment: false,
            formInvalid: false,
            productAddedToCart: false
        },

        /** @inheritdoc */
        initialize: function (config, element) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer'),
                isGuestCheckoutAllowed;

            this._super();

            isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;

            if (typeof isGuestCheckoutAllowed === 'undefined') {
                isGuestCheckoutAllowed = config.clientConfig.isGuestCheckoutAllowed;
            }

            if (config.clientConfig.isVisibleOnProductPage) {
                this.renderPayPalButtons(element);
            }

            this.declinePayment = !customer().firstname && !isGuestCheckoutAllowed;

            return this;
        },

        /** @inheritdoc */
        onClick: function () {
            var $form = $(this.productFormSelector);

            if (!this.declinePayment && !this.productAddedToCart) {
                $form.trigger('submit');
                this.formInvalid = !$form.validation('isValid');
                this.productAddedToCart = true;
            }
        },

        /** @inheritdoc */
        beforePayment: function (resolve, reject) {
            var promise = $.Deferred();

            if (this.declinePayment) {
                this.addError(this.signInMessage, 'warning');
                reject();
            } else if (this.formInvalid) {
                reject();
            } else {
                $(document).on('ajax:addToCart', function (e, data) {
                    if (_.isEmpty(data.response)) {
                        return promise.resolve();
                    }

                    return reject();
                });
                $(document).on('ajax:addToCart:error', reject);
            }

            return promise;
        },

        /**
         * After payment execute
         *
         * @param {Object} res
         * @param {Function} resolve
         * @param {Function} reject
         *
         * @return {*}
         */
        afterPayment: function (res, resolve, reject) {
            if (res.success) {
                return resolve(res.token);
            }

            this.addAlert(res['error_message']);

            return reject(new Error(res['error_message']));
        },

        /** @inheritdoc */
        prepareClientConfig: function () {
            this._super();
            this.clientConfig.quoteId = '';
            this.clientConfig.customerId = '';

            return this.clientConfig;
        },

        /** @inheritdoc */
        onError: function (err) {
            this.productAddedToCart = false;
            this._super(err);
        },

        /** @inheritdoc */
        onCancel: function (data, actions) {
            this.productAddedToCart = false;
            this._super(data, actions);
        },

        /** @inheritdoc */
        afterOnAuthorize: function (res, resolve, reject, actions) {
            this.productAddedToCart = false;

            return this._super(res, resolve, reject, actions);
        }
    });
});

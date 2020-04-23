/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Braintree/js/view/payment/method-renderer/paypal',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    _,
    Component,
    setPaymentInformationExtended,
    additionalValidators,
    fullScreenLoader
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/multishipping/paypal',
            submitButtonSelector: '#payment-continue span',
            paypalButtonSelector: '[id="parent-payment-continue"]',
            reviewButtonHtml: ''
        },

        /**
         * @override
         */
        initObservable: function () {
            this.reviewButtonHtml = $(this.paypalButtonSelector).html();

            return this._super();
        },

        /**
         * Get configuration for PayPal.
         *
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var config;

            config = this._super();
            config.flow = 'vault';
            config.enableShippingAddress = false;
            config.shippingAddressEditable = false;

            return config;
        },

        /**
         * @override
         */
        onActiveChange: function (isActive) {
            this._super(isActive);
            this.updateSubmitButton(isActive);
        },

        /**
         * @override
         */
        beforePlaceOrder: function (data) {
            this._super(data);

            this.updateSubmitButton(true);
        },

        /**
         * @override
         */
        getShippingAddress: function () {
            return {};
        },

        /**
         * @override
         */
        getData: function () {
            var data = this._super();

            data['additional_data']['is_active_payment_token_enabler'] = true;

            return data;
        },

        /**
         * @override
         */
        isActiveVault: function () {
            return true;
        },

        /**
         * Skipping order review step on checkout with multiple addresses is not allowed.
         *
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return false;
        },

        /**
         * Checks if payment method nonce is already received.
         *
         * @returns {Boolean}
         */
        isPaymentMethodNonceReceived: function () {
            return this.paymentPayload.nonce !== null;
        },

        /**
         * Updates submit button on multi-addresses checkout billing form.
         *
         * @param {Boolean} isActive
         */
        updateSubmitButton: function (isActive) {
            if (this.isPaymentMethodNonceReceived() || !isActive) {
                $(this.paypalButtonSelector).html(this.reviewButtonHtml);
            }
        },

        /**
         * @override
         */
        placeOrder: function () {
            fullScreenLoader.startLoader();
            $.when(
                setPaymentInformationExtended(
                    this.messageContainer,
                    this.getData(),
                    true
                )
            ).done(this.done.bind(this))
                .fail(this.fail.bind(this));
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        }
    });
});

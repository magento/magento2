/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert'
], function ($, Class, alert) {
    'use strict';

    return Class.extend({
        defaults: {
            $selector: null,
            selector: 'edit_form'
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            this._super();

            this.initEventHandlers();

            return this;
        },

        /**
         * Get payment code
         * @returns {String}
         */
        getCode: function () {
            return 'braintreetwo';
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {
            $('#' + this.container).find('[name="payment[token_switcher]"]')
                .on('click', this.selectPaymentMethod.bind(this));
        },

        /**
         * Select current payment token
         */
        selectPaymentMethod: function () {
            this.disableEventListeners();
            this.enableEventListeners();
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
        },

        /**
         * Pre submit for order
         * @returns {Boolean}
         */
        submitOrder: function () {
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }
            this.getPaymentMethodNonce();
        },

        /**
         * Place order
         */
        placeOrder: function () {
            this.$selector.trigger('realOrder');
        },

        /**
         * Send request to get payment method nonce
         */
        getPaymentMethodNonce: function () {
            var self = this;

            $('body').trigger('processStart');
            $.get(self.nonceUrl, {
                'public_hash': self.publicHash
            })
                .done(function (response) {
                    $('body').trigger('processStop');
                    self.setPaymentDetails(response.paymentMethodNonce);
                    self.placeOrder();
                })
                .fail(function (response) {
                    var failed = JSON.parse(response.responseText);

                    $('body').trigger('processStop');
                    self.error(failed.message);
                });
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setPaymentDetails: function (nonce) {
            this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
            this.$selector.find('[name="payment[payment_method_nonce]"]').val(nonce);
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        }
    });
});

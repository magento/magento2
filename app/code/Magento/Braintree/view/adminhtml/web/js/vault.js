/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            selector: 'edit_form',
            $container: null
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            self.$container =  $('#' + self.container);
            self.$selector.on(
                'setVaultNotActive.' + self.getCode(),
                function () {
                    self.$selector.off('submitOrder.' + self.getCode());
                }
            );
            self._super();

            self.initEventHandlers();

            return self;
        },

        /**
         * Get payment code
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {
            $(this.$container).find('[name="payment[token_switcher]"]')
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
            this.$selector.on('submitOrder.' + this.getCode(), this.submitOrder.bind(this));
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
            }).done(function (response) {
                self.setPaymentDetails(response.paymentMethodNonce);
                self.placeOrder();
            }).fail(function (response) {
                var failed = JSON.parse(response.responseText);

                self.error(failed.message);
            }).always(function () {
                $('body').trigger('processStop');
            });
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setPaymentDetails: function (nonce) {
            this.createPublicHashSelector();

            this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
            this.$container.find('#' + this.getNonceSelectorName()).val(nonce);
        },

        /**
         * Creates public hash selector
         */
        createPublicHashSelector: function () {
            var $input;

            if (this.$container.find('#' + this.getNonceSelectorName()).size() === 0) {
                $input = $('<input>').attr(
                    {
                        type: 'hidden',
                        id: this.getNonceSelectorName(),
                        name: 'payment[payment_method_nonce]'
                    }
                );

                $input.appendTo(this.$container);
                $input.prop('disabled', false);
            }
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Get selector name for nonce input
         * @returns {String}
         */
        getNonceSelectorName: function () {
            return 'nonce_' + this.getCode();
        }
    });
});

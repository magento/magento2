/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_AuthorizenetAcceptjs/js/view/payment/acceptjs-client'
], function ($, Class, alert, AcceptjsClient) {
    'use strict';

    return Class.extend({
        defaults: {
            acceptjsClient: null,
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_authorizenet_acceptjs',
            active: false,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * @{inheritdoc}
         */
        initConfig: function (config) {
            this._super();

            this.acceptjsClient = AcceptjsClient({
                environment: config.environment
            });

            return this;
        },

        /**
         * @{inheritdoc}
         */
        initObservable: function () {
            this.$selector = $('#' + this.selector);
            this._super()
                .observe('active');

            // re-init payment method events
            this.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            return this;
        },

        /**
         * Enable/disable current payment method
         *
         * @param {Object} event
         * @param {String} method
         * @returns {Object}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);

            return this;
        },

        /**
         * Triggered when payment changed
         *
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {

                return;
            }

            this.disableEventListeners();

            window.order.addExcludedPaymentMethod(this.code);

            this.enableEventListeners();
        },

        /**
         * Sets the payment details on the form
         *
         * @param {Object} tokens
         */
        setPaymentDetails: function (tokens) {
            var $ccNumber = $(this.getSelector('cc_number')),
                ccLast4 = $ccNumber.val().replace(/[^\d]/g, '').substr(-4);

            $(this.getSelector('opaque_data_descriptor')).val(tokens.opaqueDataDescriptor);
            $(this.getSelector('opaque_data_value')).val(tokens.opaqueDataValue);
            $(this.getSelector('cc_last_4')).val(ccLast4);
            $ccNumber.val('');
            $(this.getSelector('cc_exp_month')).val('');
            $(this.getSelector('cc_exp_year')).val('');

            if (this.useCvv) {
                $(this.getSelector('cc_cid')).val('');
            }
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            var authData = {},
                cardData = {},
                secureData = {};

            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');

            authData.clientKey = this.clientKey;
            authData.apiLoginID = this.apiLoginID;

            cardData.cardNumber = $(this.getSelector('cc_number')).val();
            cardData.month = $(this.getSelector('cc_exp_month')).val();
            cardData.year = $(this.getSelector('cc_exp_year')).val();

            if (this.useCvv) {
                cardData.cardCode = $(this.getSelector('cc_cid')).val();
            }

            secureData.authData = authData;
            secureData.cardData = cardData;

            this.disableEventListeners();

            this.acceptjsClient.createTokens(secureData)
                .always(function () {
                    $('body').trigger('processStop');
                    this.enableEventListeners();
                }.bind(this))
                .done(function (tokens) {
                    this.setPaymentDetails(tokens);
                    this.placeOrder();
                }.bind(this))
                .fail(function (messages) {
                    this.tokens = null;

                    if (messages.length > 0) {
                        this._showError(messages[0]);
                    }
                }.bind(this));

            return false;
        },

        /**
         * Place order
         */
        placeOrder: function () {
            this.$selector.trigger('realOrder');
        },

        /**
         * Get jQuery selector
         *
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.code + '_' + field;
        },

        /**
         * Show alert message
         *
         * @param {String} message
         */
        _showError: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.authorizenetacceptjs', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('submit');
        }

    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate',
    'Magento_AuthorizenetAcceptjs/js/view/payment/acceptjs-client'
], function ($, Class, alert, domObserver, $t, AcceptjsClient) {
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
         * Set list of observable attributes
         *
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            this._super()
                .observe('active');

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            // listen block changes
            domObserver.get('#' + self.container, function () {
                self.$selector.off('submit');
            });

            return this;
        },

        /**
         * Enable/disable current payment method
         *
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
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
                this.disableEventListeners();

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
            $(this.getSelector('opaque_data_descriptor')).val(tokens.opaqueDataDescriptor);
            $(this.getSelector('opaque_data_value')).val(tokens.opaqueDataValue);
            $([
                this.getSelector('cc_number'),
                this.getSelector('cc_exp_month'),
                this.getSelector('cc_exp_year')
            ].join(',')).val('');

            if (this.useCvv) {
                $(self.getSelector('cc_cid')).val('');
            }
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            var self = this,
                authData = {},
                cardData = {},
                secureData = {};

            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');

            // validate parent form
            if (!this.$selector.valid()) {
                return false;
            }

            authData.clientKey = this.clientKey;
            authData.apiLoginID = this.apiLoginID;

            cardData.cardNumber = $(self.getSelector('cc_number')).val();
            cardData.month = $(self.getSelector('cc_exp_month')).val();
            cardData.year = $(self.getSelector('cc_exp_year')).val();

            if (this.useCvv) {
                cardData.cardCode = $(self.getSelector('cc_cid')).val();
            }

            secureData.authData = authData;
            secureData.cardData = cardData;

            $('body').trigger('processStart');
            this.disableEventListeners();

            this.acceptjsClient.createTokens(secureData)
                .always(function () {
                    $('body').trigger('processEnd');
                })
                .done(function (tokens) {
                    self.setPaymentDetails(tokens);
                    self.enableEventListeners();
                    self.placeOrder.call(self);
                })
                .fail(function (messages) {
                    self.tokens = null;

                    if (messages.length > 0) {
                        self._showError(messages[0]);
                    }
                });

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

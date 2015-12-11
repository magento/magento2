/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_BraintreeTwo/js/view/payment/method-renderer/cc-form',
    'Magento_BraintreeTwo/js/validator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'mage/translate'
], function ($, Component, validator, vaultEnabler, $t) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_BraintreeTwo/payment/form'
        },

        initialize: function () {
            this._super();
            this.vaultEnabler = vaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getCode());

            return this;
        },

        /**
         * Init Braintree client
         */
        initBraintree: function () {
            var self = this,
                fields = self.getHostedFields();

            self._super();

            this.braintreeClient.getSdkClient().setup(this.braintreeClient.getClientToken(), 'custom', {
                id: 'co-transparent-form-braintree',
                hostedFields: fields,

                /**
                 * Triggers on payment nonce receive
                 * @param {Object} response
                 */
                onPaymentMethodReceived: function (response) {
                    self.paymentMethodNonce = response.nonce;
                    self.placeOrder();
                },

                /**
                 * Triggers on any Braintree error
                 */
                onError: function () {
                    self.paymentMethodNonce = '';
                }
            });
        },

        getData: function () {
            var data = this._super();

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        isVaultEnabled: function() {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * Get Braintree Hosted Fields
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number')
                    },
                    expirationMonth: {
                        selector: self.getSelector('expirationMonth'),
                        placeholder: $t('MM')
                    },
                    expirationYear: {
                        selector: self.getSelector('expirationYear'),
                        placeholder: $t('YY')
                    }
                };

            if (self.hasVerification()) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid')
                };
            }

            /**
             * Triggers on Hosted Field changes
             * @param {Object} event
             * @returns {Boolean}
             */
            fields.onFieldEvent = function (event) {
                if (event.isEmpty === false) {
                    self.validateCardType();
                }

                if (event.type !== 'fieldStateChange') {

                    return false;
                }

                // Handle a change in validation or card type
                if (event.target.fieldKey === 'number') {
                    self.selectedCardType(null);
                }

                if (!event.isValid) {
                    return false;
                }

                if (event.card) {
                    self.selectedCardType(
                        validator.getMageCardType(event.card.type, self.getCcAvailableTypes())
                    );
                }
            };

            return fields;
        },

        /**
         * Validate current credit card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            var $selector = $(this.getSelector('cc_number')),
                invalidClass = 'braintree-hosted-fields-invalid';

            $selector.removeClass(invalidClass);

            if (this.selectedCardType() === null) {
                $(this.getSelector('cc_number')).addClass(invalidClass);

                return false;
            }

            return true;
        },

        /**
         * Trigger order placing
         */
        placeOrderClick: function () {
            if (this.validateCardType()) {
                $(this.getSelector('submit')).trigger('click');
            }
        }
    });
});

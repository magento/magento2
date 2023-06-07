/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (Component, selectPaymentMethod, checkoutData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Vault/payment/form'
            },

            /**
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe([]);

                return this;
            },

            /**
             * @returns
             */
            selectPaymentMethod: function () {
                selectPaymentMethod(
                    {
                        method: this.getId()
                    }
                );
                checkoutData.setSelectedPaymentMethod(this.getId());

                return true;
            },

            /**
             * @returns {String}
             */
            getTitle: function () {
                return '';
            },

            /**
             * @returns {String}
             */
            getToken: function () {
                return '';
            },

            /**
             * @returns {String}
             */
            getId: function () {
                return this.index;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Get last 4 digits of card
             * @returns {String}
             */
            getMaskedCard: function () {
                return '';
            },

            /**
             * Get expiration date
             * @returns {String}
             */
            getExpirationDate: function () {
                return '';
            },

            /**
             * Get card type
             * @returns {String}
             */
            getCardType: function () {
                return '';
            },

            /**
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.ccform.icons[type]
                    : false;
            },

            /**
             * Return state of place order button.
             *
             * @return {Boolean}
             */
            isButtonActive: function () {
                return this.isActive() && this.isPlaceOrderActionAllowed();
            },

            /**
             * Check if payment is active.
             *
             * @return {Boolean}
             */
            isActive: function () {
                return this.isChecked() === this.getId();
            },

            /**
             * @returns {*}
             */
            getData: function () {
                var data = {
                    method: this.getCode()
                };

                data['additional_data'] = {};
                data['additional_data']['public_hash'] = this.getToken();

                return data;
            }
        });
    }
);

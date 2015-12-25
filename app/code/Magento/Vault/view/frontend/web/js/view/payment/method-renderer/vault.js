/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                return this.title;
            },

            /**
             * @returns {String}
             */
            getToken: function () {
                return this.token;
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
                return 'vault';
            },

            /**
             * @returns {*}
             */
            getData: function () {
                return {
                    method: this.getCode(),
                    'additional_data': {
                        token: this.getToken()
                    }
                };
            }
        });
    }
);

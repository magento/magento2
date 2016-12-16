/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/action/select-payment-method'
], function (_, quote, methodList, selectPaymentMethod) {
    'use strict';

    var freeMethodCode = 'free';

    return {
        isFreeAvailable: false,

        /**
         * Populate the list of payment methods
         * @param {Array} methods
         */
        setPaymentMethods: function (methods) {
            var self = this,
                freeMethod,
                filteredMethods,
                methodIsAvailable;

            freeMethod = _.find(methods, function (method) {
                return method.method === freeMethodCode;
            });
            this.isFreeAvailable = !!freeMethod;

            if (self.isFreeAvailable && freeMethod && quote.totals()['grand_total'] <= 0) {
                methods.splice(0, methods.length, freeMethod);
                selectPaymentMethod(freeMethod);
            }
            filteredMethods = _.without(methods, freeMethod);

            if (filteredMethods.length === 1) {
                selectPaymentMethod(filteredMethods[0]);
            } else if (quote.paymentMethod()) {
                methodIsAvailable = methods.some(function (item) {
                    return item.method === quote.paymentMethod().method;
                });
                //Unset selected payment method if not available
                if (!methodIsAvailable) {
                    selectPaymentMethod(null);
                }
            }
            methodList(methods);
        },

        /**
         * Get the list of available payment methods.
         * @returns {Array}
         */
        getAvailablePaymentMethods: function () {
            var methods = [],
                self = this;

            _.each(methodList(), function (method) {
                if (self.isFreeAvailable && (
                    quote.totals()['grand_total'] <= 0 && method.method === freeMethodCode ||
                    quote.totals()['grand_total'] > 0 && method.method !== freeMethodCode
                    ) || !self.isFreeAvailable
                ) {
                    methods.push(method);
                }
            });

            return methods;
        }
    };
});

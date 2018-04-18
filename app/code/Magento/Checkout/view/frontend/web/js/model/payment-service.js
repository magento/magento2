/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'underscore',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/select-payment-method'
    ], function (_, quote, methodList, selectPaymentMethod) {
        'use strict';

        /**
         * Free method filter
         * @param {Object} paymentMethod
         * @return {Boolean}
         */
        var isFreePaymentMethod = function (paymentMethod) {
                return paymentMethod.method === 'free';
            },

            /**
             * Grabs the grand total from quote
             * @return {Number}
             */
            getGrandTotal = function () {
                return quote.totals()['grand_total'];
            };

        return {
            isFreeAvailable: false,
            /**
             * Populate the list of payment methods
             * @param {Array} methods
             */
            setPaymentMethods: function (methods) {
                var freeMethod,
                    filteredMethods,
                    methodIsAvailable,
                    methodNames;

                freeMethod = _.find(methods, isFreePaymentMethod);
                this.isFreeAvailable = !!freeMethod;

                if (freeMethod && getGrandTotal() <= 0) {
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

                /**
                 * Overwrite methods with existing methods to preserve ko array references.
                 * This prevent ko from re-rendering those methods.
                 */
                methodNames = _.pluck(methods, 'method');
                _.map(methodList(), function (existingMethod) {
                    var existingMethodIndex = methodNames.indexOf(existingMethod.method);

                    if (existingMethodIndex !== -1) {
                        methods[existingMethodIndex] = existingMethod;
                    }
                });

                methodList(methods);
            },
            /**
             * Get the list of available payment methods.
             * @return {Array}
             */
            getAvailablePaymentMethods: function () {
                var allMethods = methodList().slice(),
                    grandTotalOverZero = getGrandTotal() > 0;

                if (!this.isFreeAvailable) {
                    return allMethods;
                }
                if (grandTotalOverZero) {
                    return _.reject(allMethods, isFreePaymentMethod);
                }
                return _.filter(allMethods, isFreePaymentMethod);

            }
        };
    }
);

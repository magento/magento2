/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/action/select-payment-method',
    'mage/utils/objects'
], function (_, quote, methodList, selectPaymentMethod, utils) {
    'use strict';

    /**
    * Free method filter
    * @param {Object} paymentMethod
    * @returns boolean
    */
    var isFreePaymentMethod = function (paymentMethod) {
        return paymentMethod.method === 'free';
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

            if (freeMethod && quote.totals()['grand_total'] <= 0) {
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
         * @returns {Array}
         */
        getAvailablePaymentMethods: function () {
            var allMethods = utils.copy(methodList()),
                grandTotalOverZero = quote.totals()['grand_total'] > 0;

            if (!this.isFreeAvailable) {
                return allMethods;
            }

            if (grandTotalOverZero) {
                return _.filter(allMethods, _.negate(isFreePaymentMethod));
            }

            return _.filter(allMethods, isFreePaymentMethod);
        }
    };
});

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Ui/js/model/errorlist',
        'mage/storage',
        'Magento_Checkout/js/action/get-totals'
    ],
    function (ko, $, quote, urlManager, paymentService, errorList, storage, getTotalsAction) {
        'use strict';
        return function (couponCode, isApplied) {
            var quoteId = quote.getQuoteId();
            var url = urlManager.getApplyCouponUrl(couponCode, quoteId);
            return storage.put(
                url
            ).done(
                function (response) {
                    if (response) {
                        isApplied(true);
                        var deferred = $.Deferred();

                        getTotalsAction([], deferred);
                        $.when(deferred).done(function() {
                            paymentService.setPaymentMethods(
                                paymentService.getAvailablePaymentMethods()
                            );
                        });
                    }
                }
            ).error(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error);
                }
            );
        };
    }
);

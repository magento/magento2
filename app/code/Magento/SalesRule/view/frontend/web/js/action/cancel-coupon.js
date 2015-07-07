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
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messageList',
        'mage/storage',
        'Magento_Checkout/js/action/get-totals',
        'mage/translate'
    ],
    function (ko, $, quote, urlManager, paymentService, errorProcessor, messageList, storage, getTotalsAction, $t) {
        'use strict';
        return function (isApplied, isLoading) {
            var quoteId = quote.getQuoteId();
            var url = urlManager.getCancelCouponUrl(quoteId);
            var message = $t('Your coupon was successfully removed');
            messageList.clear();
            return storage.delete(
                url,
                false
            ).done(
                function (response) {
                    var deferred = $.Deferred();
                    isLoading(false);
                    getTotalsAction([], deferred);
                    $.when(deferred).done(function() {
                        isApplied(false);
                        paymentService.setPaymentMethods(
                            paymentService.getAvailablePaymentMethods()
                        );
                    });
                    messageList.addSuccessMessage({'message': message});
                }
            ).fail(
                function (response) {
                    isLoading(false);
                    errorProcessor.process(response);
                }
            );
        };
    }
);

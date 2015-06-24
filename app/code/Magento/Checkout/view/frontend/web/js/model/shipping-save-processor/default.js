/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Ui/js/model/messageList'
    ],
    function (ko, quote, resourceUrlManager, storage, paymentService, messageList) {
        'use strict';
        return {
            saveShippingInformation: function() {
                var payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(response.payment_methods);
                    }
                ).fail(
                    function (response) {
                        var error = JSON.parse(response.responseText);
                        messageList.addErrorMessage(error);
                    }
                );
            }
        }
    }
);

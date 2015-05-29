/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        '../model/step-navigator',
        '../model/payment-service',
        'Magento_Ui/js/model/errorlist',
        'mage/storage',
        'underscore'
    ],
    function(quote, urlBuilder, navigator, service, errorList, storage, _) {
        "use strict";
        return function (methodData, methodInfo, callbacks) {
            var paymentMethodData = {
                "cartId": quote.getQuoteId(),
                "paymentMethod": methodData
            };

            var shippingMethodCode = quote.getSelectedShippingMethod()().slice(0).split("_"),
                shippingMethodData = {
                    "shippingCarrierCode" : shippingMethodCode.shift(),
                    "shippingMethodCode" : shippingMethodCode.join('_')
                },
                serviceUrl;
            if (quote.getCheckoutMethod()() === 'guest') {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/collect-totals', {quoteId: quote.getQuoteId()});
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/collect-totals', {});
            }

            if (quote.isVirtual()) {
                return storage.put(
                    serviceUrl,
                    JSON.stringify(paymentMethodData)
                ).done(
                    function (response) {
                        var proceed = true;
                        _.each(callbacks, function(callback) {
                            proceed = proceed && callback();
                        });
                        if (proceed) {
                            quote.setPaymentMethod(methodData.method);
                            service.setSelectedPaymentData(methodData);
                            service.setSelectedPaymentInfo(methodInfo);
                            quote.setTotals(response);
                            navigator.setCurrent('paymentMethod').goNext();
                        }
                    }
                ).error(
                    function (response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                        quote.setPaymentMethod(null);
                    }
                );
            } else {
                if (!_.isEmpty(quote.getShippingCustomOptions()())) {
                    shippingMethodData = _.extend(
                        shippingMethodData,
                        {
                            additionalData: {
                                extension_attributes: quote.getShippingCustomOptions()()
                            }
                        }
                    );
                }
                return storage.put(
                    serviceUrl,
                    JSON.stringify(_.extend(paymentMethodData, shippingMethodData))
                ).done(
                    function (response) {
                        var proceed = true;
                        _.each(callbacks, function(callback) {
                            proceed = proceed && callback();
                        });
                        if (proceed) {
                            quote.setPaymentMethod(methodData.method);
                            service.setSelectedPaymentData(methodData);
                            service.setSelectedPaymentInfo(methodInfo);
                            quote.setTotals(response);
                            navigator.setCurrent('paymentMethod').goNext();
                        }
                    }
                ).error(
                    function (response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                        quote.setPaymentMethod(null);
                    }
                );
            }
        };
    }
);

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
        'Magento_Ui/js/model/errorlist',
        'mage/storage',
        'underscore'
    ],
    function(quote, urlBuilder, navigator, errorList, storage, _) {
        "use strict";
        return function (methodView) {
            var defaultMethodData = {
                "method": methodView.getCode(),
                "po_number": null,
                "cc_owner": null,
                "cc_number": null,
                "cc_type": null,
                "cc_exp_year": null,
                "cc_exp_month": null,
                "additional_data": null
            };
            _.extend(defaultMethodData, methodView.getData());
            var paymentMethodData = {
                "cartId": quote.getQuoteId(),
                "paymentMethod": defaultMethodData
            };
            var shippingMethodCode = quote.getSelectedShippingMethod()().split("_"),
                shippingMethodData = {
                    "shippingCarrierCode" : shippingMethodCode[0],
                    "shippingMethodCode" : shippingMethodCode[1]
                };
            if (quote.isVirtual()) {
                return storage.put(
                    urlBuilder.createUrl('/carts/:quoteId/collect-totals', {quoteId: quote.getQuoteId()}),
                    JSON.stringify(_.extend(paymentMethodData))
                ).done(
                    function (response) {
                        if (methodView.afterSave()) {
                            quote.setPaymentMethod(methodView.getCode());
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
                return storage.put(
                    urlBuilder.createUrl('/carts/:quoteId/collect-totals', {quoteId: quote.getQuoteId()}),
                    JSON.stringify(_.extend(paymentMethodData, shippingMethodData))
                ).done(
                    function (response) {
                        if (methodView.afterSave()) {
                            quote.setPaymentMethod(methodView.getCode());
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

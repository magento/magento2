/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'mage/storage',
        '../model/quote',
        '../model/url-builder'

    ],
    function (ko, storage, quote, urlBuilder) {
        var totals = ko.observable();
        quote.getPaymentMethod().subscribe(function () {
            storage.get(
                urlBuilder.createUrl('/carts/:quoteId/totals', {quoteId: quote.getQuoteId()})
            ).success(
                function (data) {
                    totals(data);
                    quote.setTotals(data);
                }
            ).error(
                function (data) {
                    totals([]);
                    quote.setTotals([]);
                }
            )
        });
        return {
            getTotals: function () {
                return totals;
            }
        }
    }
);

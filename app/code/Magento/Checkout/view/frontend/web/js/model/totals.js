/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, quote) {
        'use strict';

        var quoteItems = ko.observable(quote.totals().items);

        quote.totals.subscribe(function (newValue) {
            quoteItems(newValue.items);
        });

        return {
            totals: quote.totals,
            isLoading: ko.observable(false),

            /**
             * @return {Function}
             */
            getItems: function () {
                return quoteItems;
            },

            /**
             * @param code
             * @return {*}
             */
            getSegment: function (code) {
                if (!this.totals()) {
                    return null;
                }

                for (var i in this.totals().total_segments) {
                    var total = this.totals().total_segments[i];

                    if (total.code == code) {
                        return total;
                    }
                }

                return null;
            }
        };
    }
);

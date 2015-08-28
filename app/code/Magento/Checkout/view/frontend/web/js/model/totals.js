/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote'
    ],
    function(ko, quote) {

        return {
            totals: quote.totals,
            isLoading: ko.observable(false),
            getItems: function() {
                if (!this.totals() || !this.totals().items) {
                    return [];
                }
                return this.totals().items;
            },
            getSegment: function(code) {
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

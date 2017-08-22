/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (ko, quote, customerData) {
    'use strict';

    var quoteItems = ko.observable(quote.totals().items),
        cartData = customerData.get('cart');

    quote.totals.subscribe(function (newValue) {
        quoteItems(newValue.items);
    });

    var quoteSubtotal = quote.totals().subtotal,
        subtotalAmount = cartData()['subtotalAmount'];
    if (quoteSubtotal != subtotalAmount) {
        customerData.reload(['cart'], false);
    }

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
         * @param {*} code
         * @return {*}
         */
        getSegment: function (code) {
            var i, total;

            if (!this.totals()) {
                return null;
            }

            for (i in this.totals()['total_segments']) { //eslint-disable-line guard-for-in
                total = this.totals()['total_segments'][i];

                if (total.code == code) { //eslint-disable-line eqeqeq
                    return total;
                }
            }

            return null;
        }
    };
});

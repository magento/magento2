/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Payment/payment/free'
            },

            /** Returns is method available */
            isAvailable: function() {
                return quote.totals().grand_total <= 0;
            }
        });
    }
);

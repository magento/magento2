/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Checkout/js/view/summary/shipping',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({

        /**
         * @override
         */
        isCalculated: function () {
            return !!quote.shippingMethod();
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
], function (Component, quote, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Tax/checkout/shipping_method/price'
        },
        isDisplayShippingPriceExclTax: window.checkoutConfig.isDisplayShippingPriceExclTax,
        isDisplayShippingBothPrices: window.checkoutConfig.isDisplayShippingBothPrices,

        /**
         * @param {Object} item
         * @return {Boolean}
         */
        isPriceEqual: function (item) {
            return item['price_excl_tax'] != item['price_incl_tax']; //eslint-disable-line eqeqeq
        },

        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            //todo add format data
            return priceUtils.formatPriceLocale(price, quote.getPriceFormat());
        }
    });
});

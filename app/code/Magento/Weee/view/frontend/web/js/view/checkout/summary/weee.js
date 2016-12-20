/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils'
], function (Component, quote, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Weee/checkout/summary/weee'
        },
        isIncludedInSubtotal: window.checkoutConfig.isIncludedInSubtotal,
        totals: totals.totals,

        /**
         * @returns {Number}
         */
        getWeeeTaxSegment: function () {
            var weee = totals.getSegment('weee_tax') || totals.getSegment('weee');

            if (weee !== null && weee.hasOwnProperty('value')) {
                return weee.value;
            }

            return 0;
        },

        /**
         * Get weee value
         * @returns {String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getWeeeTaxSegment());
        },

        /**
         * Weee display flag
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getWeeeTaxSegment() > 0;
        }
    });
});

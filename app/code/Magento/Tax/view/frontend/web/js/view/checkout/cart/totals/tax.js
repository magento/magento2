/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Tax/js/view/checkout/summary/tax',
    'Magento_Checkout/js/model/totals'
], function (Component, totals) {
    'use strict';

    var isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed,
        isZeroTaxDisplayed = window.checkoutConfig.isZeroTaxDisplayed;

    return Component.extend({
        /**
         * @override
         */
        ifShowValue: function () {
            if (this.getPureValue() === 0) {
                return isZeroTaxDisplayed;
            }

            return true;
        },

        /**
         * @override
         */
        ifShowDetails: function () {
            return this.getPureValue() > 0 && isFullTaxSummaryDisplayed;
        },

        /**
         * @override
         */
        isCalculated: function () {
            return this.totals() && totals.getSegment('tax') !== null;
        }
    });
});

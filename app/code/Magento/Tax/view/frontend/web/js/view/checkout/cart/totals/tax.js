/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
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
            if (this.isFullMode() && this.getPureValue() == 0) { //eslint-disable-line eqeqeq
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
        },

        formatPercent: function (percent) {
            try {
                var locale = (window.checkoutConfig && window.checkoutConfig.locale) 
                    ? window.checkoutConfig.locale.replace('_', '-') 
                    : 'de-DE'; // fallback
        
                if (typeof percent === 'number' || (typeof percent === 'string' && percent.match(/^[\d.]+$/))) {
                    return parseFloat(percent).toLocaleString(locale, {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 2
                    });
                }
            } catch (e) {
                console.warn('Percent formatting failed:', e);
            }
            return percent;
        }           
    });
});

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @param {Object} item
         * @return {Boolean}
         */
        isDisplayPriceWithWeeeDetails: function (item) {
            if (!parseFloat(item['weee_tax_applied_amount']) || parseFloat(item['weee_tax_applied_amount'] <= 0)) {
                return false;
            }

            return window.checkoutConfig.isDisplayPriceWithWeeeDetails;
        },

        /**
         * @param {Object} item
         * @return {Boolean}
         */
        isDisplayFinalPrice: function (item) {
            if (!parseFloat(item['weee_tax_applied_amount'])) {
                return false;
            }

            return window.checkoutConfig.isDisplayFinalPrice;
        },

        /**
         * @param {Object} item
         * @return {Array}
         */
        getWeeeTaxApplied: function (item) {
            if (item['weee_tax_applied']) {
                return JSON.parse(item['weee_tax_applied']);
            }

            return [];
        }
    });
});

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component,quote, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Weee/checkout/summary/weee'
            },
            isIncludedInSubtotal: window.checkoutConfig.isIncludedInSubtotal,
            totals: totals.totals,
            getValue: function() {
                return this.getFormattedPrice(this.totals()['weee_tax_applied_amount']);
            },
            isDisplayed: function() {
                return this.isFullMode() && this.totals()['weee_tax_applied_amount'] > 0;
            }
        });
    }
);

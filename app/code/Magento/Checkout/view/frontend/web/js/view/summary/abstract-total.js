/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (Component, quote, priceUtils, totals, stepNavigator) {
        "use strict";
        return Component.extend({
            shippingAvailableFlag: undefined,
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getTotals: function() {
                return totals.totals();
            },
            isShippingAvailable: function() {
                if (undefined !== this.shippingAvailableFlag) {
                    return this.shippingAvailableFlag;
                }
                this.shippingAvailableFlag = stepNavigator.isAvailable('shipping');
                return this.shippingAvailableFlag;
            },
            isFullMode: function() {
                if (!this.getTotals()) {
                    return false;
                }
                return !this.isShippingAvailable() || stepNavigator.isProcessed('shipping');
            }
        });
    }
);

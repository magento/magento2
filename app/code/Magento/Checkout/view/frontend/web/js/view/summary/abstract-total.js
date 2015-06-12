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
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getTotals: function() {
                return totals.totals();
            },
            getTotalsMode: function() {
                if (!this.getTotals() || !this.getTotals()['mode']) {
                    return null;
                }
                var mode = this.getTotals()['mode'];
                return mode();
            }
        });
    }
);

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/sidebar'
    ],
    function (Component, quote, priceUtils, totals, sidebarModel) {
        'use strict';
        return Component.extend({
            isLoading: totals.isLoading,
            getQuantity: function() {
                if (totals.totals()) {
                    return parseFloat(totals.totals().items_qty);
                }
                return 0;
            },
            getPureValue: function() {
                if (totals.totals()) {
                    return parseFloat(totals.getSegment('grand_total').value);
                }
                return 0;
            },
            showSidebar: function() {
                sidebarModel.show();
            },
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);


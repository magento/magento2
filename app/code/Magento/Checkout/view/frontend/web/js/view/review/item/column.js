/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        '../../../model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        "use strict";
        var ownClass = '';
        var columnTitle = '';
        return Component.extend({
            defaults: {
                headerClass: null,
                ownClass: ownClass,
                columnTitle: columnTitle,
                template: 'Magento_Checkout/review/item/column'
            },
            getClass: function() {
                return 'col ' + this.ownClass;
            },
            getHeaderClass: function() {
                if (this.headerClass) {
                    return this.headerClass;
                }
                return 'col ' + this.ownClass;
            },
            getColName: function() {
                return this.columnTitle;
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            },
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            }
        });
    }
);

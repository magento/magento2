/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/review/item/column'
    ],
    function (column) {
        "use strict";
        var displayPriceMode = window.checkoutConfig.reviewItemPriceDisplayMode || 'including';
        return column.extend({
            defaults: {
                displayPriceMode: displayPriceMode,
                ownClass: 'price',
                columnTitle: 'Price',
                template: 'Magento_Tax/checkout/review/item/columns/price'
            },
            isPriceInclTaxDisplayed: function() {
                return 'both' == this.displayPriceMode || 'including' == this.displayPriceMode;
            },
            isPriceExclTaxDisplayed: function() {
                return 'both' == this.displayPriceMode || 'excluding' == this.displayPriceMode;
            },
            isBothPricesDisplayed: function() {
                return 'both' == this.displayPriceMode;
            },
            getPriceExclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.price);
            },
            getPriceInclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.price_incl_tax);
            }
        });
    }
);

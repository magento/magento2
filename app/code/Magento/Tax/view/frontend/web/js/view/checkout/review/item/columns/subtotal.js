/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/review/item/columns/subtotal'
    ],
    function (subtotal) {
        "use strict";
        var displayPriceMode = window.checkoutConfig.reviewItemPriceDisplayMode || 'including';
        return subtotal.extend({
            defaults: {
                displayPriceMode: displayPriceMode,
                ownClass: 'subtotal',
                columnTitle: 'Subtotal',
                template: 'Magento_Tax/checkout/review/item/columns/subtotal'
            },
            isPriceInclTaxDisplayed: function() {
                return 'both' == displayPriceMode || 'including' == displayPriceMode;
            },
            isPriceExclTaxDisplayed: function() {
                return 'both' == displayPriceMode || 'excluding' == displayPriceMode;
            },
            getValueInclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem['row_total_incl_tax']);
            },
            getValueExclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem['row_total']);
            }

        });
    }
);

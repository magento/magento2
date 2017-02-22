/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/item/details/subtotal'
    ],
    function (subtotal) {
        'use strict';

        var displayPriceMode = window.checkoutConfig.reviewItemPriceDisplayMode || 'including';

        return subtotal.extend({
            defaults: {
                displayPriceMode: displayPriceMode,
                template: 'Magento_Tax/checkout/summary/item/details/subtotal'
            },
            isPriceInclTaxDisplayed: function () {
                return 'both' == displayPriceMode || 'including' == displayPriceMode;
            },
            isPriceExclTaxDisplayed: function () {
                return 'both' == displayPriceMode || 'excluding' == displayPriceMode;
            },
            getValueInclTax: function (quoteItem) {
                return this.getFormattedPrice(quoteItem['row_total_incl_tax']);
            },
            getValueExclTax: function (quoteItem) {
                return this.getFormattedPrice(quoteItem['row_total']);
            }

        });
    }
);

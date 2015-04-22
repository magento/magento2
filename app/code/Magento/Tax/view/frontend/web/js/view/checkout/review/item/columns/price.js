/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/review/item/column'
    ],
    function (column) {
        "use strict";
        return column.extend({
            defaults: {
                headerClass: 'price',
                displayPriceMode: 'both',
                ownClass: 'price-including-tax',
                columnTitle: 'Price',
                template: 'Magento_Tax/checkout/review/item/columns/price'
            },
            displayPriceInclTax: function() {
                return 'both' == this.displayPriceMode || 'including' == this.displayPriceMode;
            },
            displayPriceExclTax: function() {
                return 'both' == this.displayPriceMode || 'excluding' == this.displayPriceMode;
            },
            displayBothPrices: function() {
                return 'both' == this.displayPriceMode;
            },
            getPriceExclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.price);
            },
            getPriceInclTax: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.price_incl_tax);
            },
            getClass: function() {
                if (this.displayBothPrices || this.displayPriceInclTax) {
                    return 'price-including-tax';
                } else {
                    return 'price-excluding-tax';
                }
            }
        });
    }
);

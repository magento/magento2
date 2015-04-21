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
                displayBothPrices: true,
                displayPriceInclTax: true,
                displayPriceExclTax: true,
                ownClass: 'price-including-tax',
                columnTitle: 'Price',
                template: 'Magento_Tax/checkout/review/item/columns/price'
            },
            displayPriceInclTax: function() {
                return this.displayBothPrices || this.displayPriceInclTax;
            },
            displayPriceExclTax: function() {
                return this.displayBothPrices || this.displayPriceExclTax;
            },
            getPriceExclTax: function(quoteItem) {
                console.log(quoteItem);
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

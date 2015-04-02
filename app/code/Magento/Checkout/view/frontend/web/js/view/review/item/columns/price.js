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
        '../column',
    ],
    function (column) {
        return column.extend({
            defaults: {
                ownClass: 'price',
                columnTitle: 'Price',
                template: 'Magento_Checkout/review/item/columns/price'
            },
            getValue: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.price);
            }
        });
    }
);

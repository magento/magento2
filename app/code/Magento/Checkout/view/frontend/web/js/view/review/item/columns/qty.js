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
                ownClass: 'qty',
                columnTitle: 'Qty',
                template: 'Magento_Checkout/review/item/columns/qty'
            },
            getValue: function(quoteItem) {
                return quoteItem.qty;
            }
        });
    }
);

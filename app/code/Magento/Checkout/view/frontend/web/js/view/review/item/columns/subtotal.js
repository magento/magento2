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
                ownClass: 'subtotal',
                columnTitle: 'Subtotal'
            },
            getValue: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.row_total);
            }
        });
    }
);

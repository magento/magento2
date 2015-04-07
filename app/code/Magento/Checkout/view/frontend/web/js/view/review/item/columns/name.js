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
        '../column'
    ],
    function (column) {
        "use strict";
        return column.extend({
            defaults: {
                ownClass: 'name',
                columnTitle: 'Product Name',
                template: 'Magento_Checkout/review/item/columns/name'
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            }
        });
    }
);

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
        var ownClass = '';
        var columnTitle = '';
        return Component.extend({
            defaults: {
                ownClass: ownClass,
                columnTitle: columnTitle,
                template: 'Magento_Checkout/review/item/column'
            },
            getClass: function() {
                return 'col ' + this.ownClass;
            },
            getColName: function() {
                return this.columnTitle;
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            }
        });
    }
);

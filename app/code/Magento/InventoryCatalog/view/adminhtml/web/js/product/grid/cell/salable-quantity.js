/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventoryCatalog/product/grid/cell/salable-quantity.html'
        },

        /**
         * Get salable quantity data (stock name and salable qty)
         *
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSalableQuantityData: function (record) {
            var result = record[this.index] ? record[this.index] : [];

            return result;
        }
    });
});

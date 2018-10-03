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
            bodyTmpl: 'Magento_InventoryAdminUi/stock/grid/cell/assigned-sources-cell.html'
        },

        /**
         * Get sales channels grouped by type
         *
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSourcesAssignedToStockOrderedByPriority: function (record) {
            var result = [];

            _.each(record[this.index], function (source) {
                result.push({
                    source_code: source.source_code,
                    name: source.name
                });
            });

            return result;
        }
    });
});

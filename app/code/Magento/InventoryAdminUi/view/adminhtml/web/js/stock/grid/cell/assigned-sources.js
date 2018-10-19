/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventoryAdminUi/stock/grid/cell/assigned-sources-cell.html'
        },

        /**
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSourcesAssignedToStockOrderedByPriority: function (record) {
            var result = [];

            _.each(record[this.index], function (source) {
                result.push({
                    sourceCode: source.sourceCode,
                    name: source.name
                });
            });

            return result;
        }
    });
});

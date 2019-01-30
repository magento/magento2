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
            bodyTmpl: 'Magento_InventoryCatalogAdminUi/product/grid/cell/source-items.html',
            itemsToDisplay: 5
        },

        /**
         * Get source items data (source name and qty)
         *
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSourceItemsData: function (record) {
            return record[this.index] ? record[this.index] : [];
        },

        /**
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSourceItemsDataCut: function (record) {
            return this.getSourceItemsData(record).slice(0, this.itemsToDisplay);
        }
    });
});

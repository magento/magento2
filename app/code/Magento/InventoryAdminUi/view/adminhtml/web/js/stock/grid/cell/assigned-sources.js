/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'underscore',
    'mage/translate'
], function (Column, _, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventoryAdminUi/stock/grid/cell/assigned-sources-cell.html',
            showFullListDescription: $t('Show more...'),
            itemsToDisplay: 5
        },

        /**
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super();

            return this;
        },

        /**
         *
         * @param record
         * @returns {Array}
         */
        getTooltipData: function (record) {
            return record[this.index].map(function (source) {
                return {
                    sourceCode: source.sourceCode,
                    name: source.name
                }
            });
        },

        /**
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSourcesAssignedToStockOrderedByPriority: function (record) {
            return this.getTooltipData(record).slice(0, this.itemsToDisplay)
        }
    });
});

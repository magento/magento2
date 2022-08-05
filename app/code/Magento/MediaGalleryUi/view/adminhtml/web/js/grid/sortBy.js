/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/sortBy'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            columnIndexMap: {}
        },

        /**
         * Prepared sort order options
         */
        preparedOptions: function (columns) {
            var index = 0,
                sortBy;

            if (columns && columns.length > 0) {
                columns.map(function (column) {
                    if (column.sortable === true) {
                        sortBy = column['sort_by'] || {};

                        if (sortBy.excluded) {
                            return;
                        }

                        this.options.push({
                            value: column.index,
                            label: column.label,
                            sortByField: sortBy.field,
                            sortDirection: sortBy.direction
                        });

                        this.columnIndexMap[column.index] = index++;

                        this.isVisible(true);
                    } else {
                        this.isVisible(false);
                    }
                }.bind(this));
            }
        },

        /**
         * Apply changes
         */
        applyChanges: function () {
            var column = this.getColumn(this.selectedOption());

            this.applied({
                field: column.sortByField || this.selectedOption(),
                direction: column.sortDirection || this.sorting
            });
        },

        /**
         * Get column by index
         *
         * @param {String} optionIndex
         * @returns {Object}
         */
        getColumn: function (optionIndex) {
            return this.options[this.columnIndexMap[optionIndex]];
        },

        /**
         * Select default option
         */
        selectDefaultOption: function () {
            this.selectedOption(this.options[0].value);
        }
    });
});

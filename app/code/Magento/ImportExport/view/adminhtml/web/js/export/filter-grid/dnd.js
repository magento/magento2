/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/dnd'
], function (_, Dnd) {
    'use strict';

    return Dnd.extend({

        /**
         * Creates clone of a target table with only specified column visible.
         *
         * @param {HTMLTableHeaderCellElement} elem - Dragging column.
         * @returns {Dnd} Chainbale.
         */
        _cloneTable: function (elem) {
            var columnIndex = this._getColumnIndex(elem),
                filterRow   = this.dragTable.tHead.lastElementChild,
                filterCells = _.toArray(filterRow.cells);

            this._super(elem);

            filterCells.forEach(function (td, index) {
                if (index !== columnIndex) {
                    filterRow.removeChild(td);
                }
            });

            return this;
        }
    });
});

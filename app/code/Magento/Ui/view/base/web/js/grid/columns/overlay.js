/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        /**
         * If overlay should be visible
         *
         * @param {Object} row
         * @returns {Boolean}
         */
        isVisible: function (row) {
            return !!row[this.index];
        },

        /**
         * Get overlay label
         *
         * @param {Object} row
         * @returns {String}
         */
        getLabel: function (row) {
            return row[this.index];
        },

        /**
         * Returns top displacement of overlay according to image height
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Object}
         */
        getStyles: function (record) {
            var height = record.styles()['height'].replace('px', '');
            return {top: (height - 50) + 'px'};
        }
    });
});

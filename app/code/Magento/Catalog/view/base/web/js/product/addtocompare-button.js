/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/uenc-processor',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Column, uencProcessor, columnStatusValidator) {
    'use strict';

    return Column.extend({
        defaults: {
            label: ''
        },

        /**
         * Prepare Data-Post data that will be used in data-mage-init
         *
         * @param {Object} row
         * @returns {Array}
         */
        getDataPost: function (row) {
            return uencProcessor(row['add_to_compare_button'].url ||
                    row['add_to_compare_button']['post_data']);
        },

        /**
         * Depends on this option, "Add to compare" button can be shown or hide. Depends on  backend configuration
         *
         * @returns {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'add_to_compare', 'show_buttons');
        },

        /**
         * Get button label.
         *
         * @return {String}
         */
        getLabel: function () {
            return this.label;
        }
    });
});

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
            bodyTmpl: 'Magento_ImportExport/export/filter-grid/columns/filter',
            templates: {
                base: 'ui/grid/cells/text',
                input: 'Magento_ImportExport/export/filter-grid/columns/filter/input',
            },
            filters: {}
        },

        /**
         * Returns path to the template based on data type.
         *
         * @param {Object} row
         * @returns {String}
         */
        getTemplateByType: function (row) {
            if (this.templates[row.filterType]) {
                return this.templates[row.filterType];
            }

            return this.templates.base;
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column',
], function (_, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_ImportExport/export/filter-grid/columns/filter',
            templates: {
                base: 'ui/grid/cells/text',
                input: 'Magento_ImportExport/export/filter-grid/columns/filter/input',
                select: 'Magento_ImportExport/export/filter-grid/columns/filter/select'
            },
            filters: {},
            notSelectedLabel: '-- Not Selected --'
        },

        /**
         * Returns path to the template based on data type.
         *
         * @param {Object} row
         * @returns {String}
         */
        getTemplateByType: function (row) {
            let filter = row[this.index];

            if (_.isObject(filter) && this.templates[filter.type]) {
                return this.templates[filter.type];
            }

            return this.templates.base;
        },

        /**
         * Retrieves options for selects, including "not selected" value.
         *
         * @param {Object} row
         * @returns {Array}
         */
        getOptions: function (row) {
            let options = _.toArray(row[this.index].options);

            options.unshift({
                value: '',
                label: $t(this.notSelectedLabel)
            });

            return options;
        }
    });
});

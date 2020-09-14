/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/list/column-status-validator',
    'escaper'
], function (Column, columnStatusValidator, escaper) {
    'use strict';

    return Column.extend({
        defaults: {
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Depends on this option, product name can be shown or hide. Depends on  backend configuration
         *
         * @returns {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'name', 'show_attributes');
        },

        /**
         * Name column.
         *
         * @param {String} name
         * @returns {String}
         */
        getNameUnsanitizedHtml: function (name) {
            return escaper.escapeHtml(name, this.allowedTags);
        }
    });
});

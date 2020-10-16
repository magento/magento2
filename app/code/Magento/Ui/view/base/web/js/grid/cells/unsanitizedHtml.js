/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/column',
    'escaper'
], function (Column, escaper) {
    'use strict';

    return Column.extend({
        defaults: {
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Name column.
         *
         * @param {String} label
         * @returns {String}
         */
        getUnsanitizedHtml: function (label) {
            return escaper.escapeHtml(label, this.allowedTags);
        }
    });
});

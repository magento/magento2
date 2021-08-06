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
        getSafeHtml: function (label) {
            return escaper.escapeHtml(label, this.allowedTags);
        },

        /**
         * UnsanitizedHtml version of getSafeHtml.
         *
         * @param {String} label
         * @returns {String}
         */
        getSafeUnsanitizedHtml: function (label) {
            return this.getSafeHtml(label);
        }
    });
});

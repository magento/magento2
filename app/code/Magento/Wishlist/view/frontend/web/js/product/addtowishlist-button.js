/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/uenc-processor',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Element, uencProcessor, columnStatusValidator) {
    'use strict';

    return Element.extend({
        defaults: {
            label: ''
        },

        /**
         * Get request POST data.
         *
         * @param {Object} row
         * @return {String}
         */
        getDataPost: function (row) {
            return uencProcessor(row['extension_attributes']['wishlist_button'].url);
        },

        /**
         * Check if component must be shown.
         *
         * @return {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'add_to_wishlist', 'show_buttons');
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

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'escaper'
], function (Component, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details',
            allowedTags: ['b', 'strong', 'i', 'em', 'u']
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getNameUnsanitizedHtml: function (quoteItem) {
            var txt = document.createElement('textarea');

            txt.innerHTML = quoteItem.name;

            return escaper.escapeHtml(txt.value, this.allowedTags);
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'prototype'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getNameUnsanitizedHtml: function (quoteItem) {
            var txt = document.createElement('textarea');

            txt.innerHTML = quoteItem.name;

            return txt.value.escapeHTML();
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

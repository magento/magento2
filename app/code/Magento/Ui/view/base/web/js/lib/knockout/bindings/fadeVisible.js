/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko'
], function ($, ko) {
    'use strict';

    ko.bindingHandlers.fadeVisible = {
        /**
         * Initially set the element to be instantly visible/hidden depending on the value.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        init: function (element, valueAccessor) {
            var value = valueAccessor();

            // Use "unwrapObservable" so we can handle values that may or may not be observable
            $(element).toggle(ko.unwrap(value));
        },

        /**
         * Whenever the value subsequently changes, slowly fade the element in or out.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        update: function (element, valueAccessor) {
            var value = valueAccessor();

            ko.unwrap(value) ? $(element).fadeIn() : $(element).fadeOut();
        }
    };
});

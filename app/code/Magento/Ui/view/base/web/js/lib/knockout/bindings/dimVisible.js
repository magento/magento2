/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'ko'
], function ($, ko) {
    'use strict';

    ko.bindingHandlers.dimVisible = {
        /**
         * Initially set the element to be instantly visible/hidden depending on the value.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        init: function (element, valueAccessor) {
            let value = valueAccessor();

            // Use "unwrapObservable" so we can handle values that may or may not be observable
            if (ko.unwrap(value)) {
                $(element).css('visibility','visible').css('height','auto').css('position','relative');
            } else {
                $(element).css('visibility', 'hidden').css('height', '0').css('position', 'absolute');
            }
        },

        /**
         * Whenever the value subsequently changes, update the state of the element accordingly.
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         */
        update: function (element, valueAccessor) {
            let value = valueAccessor();

            if (ko.unwrap(value)) {
                $(element).css('visibility', 'visible').css('height', 'auto').css('position', 'relative');
            } else {
                $(element).css('visibility', 'hidden').css('height', '0').css('position', 'absolute');
            }
        }
    };
});

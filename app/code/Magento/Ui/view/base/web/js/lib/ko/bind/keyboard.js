/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore'
], function (ko) {
    'use strict';

    ko.bindingHandlers.keyboard = {

        /**
         * Attaches keypress handlers to element
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         * @param  {Object} allBindings - all bindings object
         * @param  {Object} viewModel - reference to viewmodel
         */
        init: function (el, valueAccessor, allBindings, viewModel) {
            var map = valueAccessor();

            ko.utils.registerEventHandler(el, 'keyup', function (e) {
                var callback = map[e.keyCode];

                if (callback) {
                    callback.call(viewModel);
                }
            });
        }
    };
});

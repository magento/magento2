/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates outerClick binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    function clickWrapper(elem, callback, e) {
        var target = e.target;

        if (target !== elem && !elem.contains(target)) {
            callback();
        }
    }

    ko.bindingHandlers.outerClick = {

        /**
         * Attaches click handler to document
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         * @param  {Object} allBindings - all bindings object
         * @param  {Object} viewModel - reference to viewmodel
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var callback = valueAccessor(),
                wrapper;

            callback = callback.bind(viewModel);
            wrapper = clickWrapper.bind(null, element, callback);

            $(document).on('click', wrapper);

            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                $(document).off('click', wrapper);
            });
        }
    };
});

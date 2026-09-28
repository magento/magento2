/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define([
    'ko',
    '../template/renderer'
], function (ko, renderer) {
    'use strict';

    ko.bindingHandlers.afterRender = {

        /**
         * Binding init callback.
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var callback = valueAccessor();

            if (typeof callback === 'function') {
                callback.call(viewModel, element, viewModel);
            }
        }
    };

    renderer.addAttribute('afterRender');
});

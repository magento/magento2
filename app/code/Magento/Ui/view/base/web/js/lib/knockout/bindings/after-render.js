/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

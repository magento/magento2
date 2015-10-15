/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko'
], function (ko) {
    'use strict';

    ko.bindingHandlers.afterRender = {

        /**
         * Binding init callback.
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var callback = valueAccessor();

            if (typeof callback === 'function') {
                callback(element, viewModel);
            }
        }
    };
});

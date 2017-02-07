/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mage/apply/main'
], function (ko, _, mage) {
    'use strict';

    ko.bindingHandlers.mageInit = {
        /**
         * Initializes components assigned to HTML elements.
         *
         * @param {HTMLElement} el
         * @param {Function} valueAccessor
         */
        init: function (el, valueAccessor) {
            var data = valueAccessor();

            _.each(data, function (config, component) {
                mage.applyFor(el, config, component);
            });
        }
    };
});

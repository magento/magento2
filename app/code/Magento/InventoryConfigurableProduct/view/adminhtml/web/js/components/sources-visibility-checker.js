/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'uiComponent'
], function (registry, component) {
    'use strict';

    return component.extend({
        defaults: {
            sourcesIndex: ''
        },

        /**
         * Hide source tab if convert product to configurable and show it if to simple.
         */
        applySourcesConfiguration: function (visibleMatrix) {
            registry.get('index = ' + this.sourcesIndex).visible(!visibleMatrix);
        }
    });
});

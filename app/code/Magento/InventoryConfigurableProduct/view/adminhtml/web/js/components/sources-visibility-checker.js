/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'uiComponent'
], function (_, registry, component) {
    'use strict';

    return component.extend({
        defaults: {
            sourcesIndex: ''
        },

        /**
         * Hide source tab if convert product to configurable and show it if to simple.
         */
        applySourcesConfiguration: function (visibleMatrix) {
            var source = registry.get('index = ' + this.sourcesIndex);

            if (!_.isUndefined(source)) {
                source.visible(!visibleMatrix);
            }
        }
    });
});

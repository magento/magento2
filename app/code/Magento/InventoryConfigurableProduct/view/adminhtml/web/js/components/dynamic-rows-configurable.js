/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable'
], function (registry, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            matrixIndex: '',
            sourcesIndex: ''
        },

        /** @inheritdoc */
        addChild: function (data, index, prop) {
            this._super(data, index, prop);

            this.applySourcesConfiguration();
        },

        /** @inheritdoc */
        deleteRecord: function (index) {
            this._super(index);

            this.applySourcesConfiguration();
        },

        /**
         * Hide source tab if convert product to configurable and show it if to simple.
         */
        applySourcesConfiguration: function () {
            var matrix = registry.get('index = ' + this.matrixIndex),
                sources = registry.get('index = ' + this.sourcesIndex),
                isEmpty,
                isVisibleSources;

            if (typeof matrix !== 'undefined' && typeof sources !== 'undefined') {
                isEmpty = matrix.isEmpty();
                isVisibleSources = sources.visible();

                if (!isEmpty && isVisibleSources) {
                    sources.visible(false);
                } else if (isEmpty && !isVisibleSources) {
                    sources.visible(true);
                }
            }
        }
    });
});

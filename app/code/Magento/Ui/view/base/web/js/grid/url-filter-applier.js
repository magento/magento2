/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore',
], function (Component, _) {
    'use strict';

    return Component.extend({
        defaults: {
            filterProvider: 'componentType = filters, ns = ${ $.ns }',
            filters: null,
            modules: {
                filterComponent: '${ $.filterProvider }',
            }
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.apply();

            return this;
        },

        /**
         * Apply filter
         */
        apply: function () {
            if (_.isUndefined(this.filterComponent())) {
                setTimeout(function () {this.apply()}.bind(this), 100);
            } else {
                if (!_.isNull(this.filters)) {
                    this.filterComponent().setData(this.filters, false);
                    this.filterComponent().apply();
                }
            }
        }
    });
});

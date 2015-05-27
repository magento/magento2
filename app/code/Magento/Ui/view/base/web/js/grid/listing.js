/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Ui/js/lib/spinner'
], function (Component, loader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/listing',
            imports: {
                rows: '${ $.provider }:data.items'
            },
            listens: {
                '${ $.provider }:reload': 'showLoader',
                '${ $.provider }:reloaded': 'hideLoader'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('rows');

            return this;
        },

        /**
         * Hides loader.
         */
        hideLoader: function () {
            loader.get(this.name).hide();
        },

        /**
         * Shows loader.
         */
        showLoader: function () {
            loader.get(this.name).show();
        },

        /**
         * Returns total number of columns in grid.
         *
         * @returns {Number}
         */
        getColspan: function () {
            return this.elems().length;
        },

        /**
         * Checks if grid has data.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.rows().length;
        }
    });
});

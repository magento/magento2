/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore'
], function (Component, _) {
    'use strict';

    return Component.extend({
        defaults: {
            listingNamespace: null,
            filterProvider: 'componentType = filters, ns = ${ $.listingNamespace }',
            filterKey: 'filters',
            searchString: location.search,
            modules: {
                filterComponent: '${ $.filterProvider }'
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
            var urlFilter = this.getFilterParam(this.searchString);

            if (_.isUndefined(this.filterComponent())) {
                setTimeout(function () {
                    this.apply();
                }.bind(this), 100);

                return;
            }

            if (Object.keys(urlFilter).length) {
                this.filterComponent().setData(urlFilter, false);
                this.filterComponent().apply();
            }
        },

        /**
         * Get filter param from url
         *
         * @returns {Object}
         */
        getFilterParam: function (url) {
            var searchString = decodeURI(url),
                itemArray;

            return _.chain(searchString.slice(1).split('&'))
                .map(function (item) {
                    if (item && item.search(this.filterKey) !== -1) {
                        itemArray = item.split('=');

                        itemArray[0] = itemArray[0].replace(this.filterKey, '')
                                .replace(/[\[\]]/g, '');

                        return itemArray;
                    }
                }.bind(this))
                .compact()
                .object()
                .value();
        }
    });
});

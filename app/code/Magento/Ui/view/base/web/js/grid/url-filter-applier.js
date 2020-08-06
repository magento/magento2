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
            selectComponentName: null,
            selectProvider: 'index = ${ $.selectComponentName }, ns = ${ $.listingNamespace }',
            filterProvider: 'componentType = filters, ns = ${ $.listingNamespace }',
            filterKey: 'filters',
            optionsKey: 'options',
            searchString: location.search,
            modules: {
                filterComponent: '${ $.filterProvider }',
                selectComponent: '${ $.selectProvider }'
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
            var urlFilter = this.getFilterParam(this.searchString),
                options = this.getOptionsParam(this.searchString);

            if (_.isUndefined(this.filterComponent()) ||
                !_.isNull(this.selectComponentName) && _.isUndefined(this.selectComponent())) {
                setTimeout(function () {
                    this.apply();
                }.bind(this), 100);

                return;
            }

            if (Object.keys(options).length) {
                this.selectComponent().options(options);
                this.selectComponent().cacheOptions.plain = options;
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

                        if (itemArray[1].search('\\[') === 0) {
                            itemArray[1] = itemArray[1].replace(/[\[\]]/g, '').split(',');
                        }

                        itemArray[0] = itemArray[0].replace(this.filterKey, '')
                                .replace(/[\[\]]/g, '');

                        return itemArray;
                    }
                }.bind(this))
                .compact()
                .object()
                .value();
        },

        /**
         * Get Filter options
         *
         * @param {String} url
         */
        getOptionsParam: function (url) {
            var params = [],
                chunks,
                chunk,
                values = {},
                options,
                searchString = decodeURI(url);

            _.chain(searchString.slice(1).split('&'))
                .map(function (item, k) {
                    if (item && item.search(this.optionsKey) !== -1) {
                        chunks = item.substring(item.indexOf('?') + 1).split('&');

                        for (var i = 0; i < chunks.length ; i++) {
                            options = chunks[i].substring(item.indexOf('[]') + 3).replace(/[\[\]]/g, '').split(',');
                            options.map(function (item) {
                                chunk = item.split('=');
                                values[chunk[0]] = chunk[1];
                            }.bind(this));
                        }
                        params[k - 1] = values;
                        values = {};
                    }
                }.bind(this));

            return params;
        }

    });
});

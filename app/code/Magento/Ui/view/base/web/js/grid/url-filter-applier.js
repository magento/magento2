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
            filterComponentName: null,
            selectProvider: 'index = ${ $.filterComponentName }, ns = ${ $.listingNamespace }',
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
                !_.isNull(this.filterComponentName) && _.isUndefined(this.selectComponent())) {
                setTimeout(function () {
                    this.apply();
                }.bind(this), 100);

                return;
            }

            if (Object.keys(options).length) {
                this.selectComponent().options(options);
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
                values,
                i,
                options,
                searchString = decodeURI(url);

            _.chain(searchString.slice(1).split('&'))
                .map(function (item, k) {
                    if (item && item.search(this.optionsKey) !== -1) {
                        chunks = item.substring(item.indexOf('?') + 1).split('&');

                        for (i = 0; i < chunks.length; i++) {
                            options = chunks[i].substring(item.indexOf('[]') + 3)
                                .replace(/[\[\]]/g, '')
                                .split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);
                            values = this.getOptionsValues(options);

                        }
                        params[k - 1] = values;
                    }
                }.bind(this));

            return params;
        },

        /**
         * Return options values as array
         *
         * @param {Array} options
         * @return {Array}
         */
        getOptionsValues: function (options) {
            var values = {},
                j;

            for (j = 0; j < options.length; j++) {
                values[options[j].split('=')[0]] = options[j].split('=')[1];
            }

            return values;
        }
    });
});

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
                this.selectComponent().cacheOptions.plain = [options];
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
            var searchString = decodeURI(url),
                options;
            options = Object.fromEntries(new URLSearchParams(searchString));
            delete options['filters[asset_id]'];

            return options;
        }
    });
});

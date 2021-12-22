/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore',
    'jquery'
], function (Component, _, $) {
    'use strict';

    return Component.extend({
        defaults: {
            listingNamespace: null,
            bookmarkProvider: 'componentType = bookmark, ns = ${ $.listingNamespace }',
            filterProvider: 'componentType = filters, ns = ${ $.listingNamespace }',
            filterKey: 'filters',
            searchString: location.search,
            modules: {
                bookmarks: '${ $.bookmarkProvider }',
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
            var urlFilter = this.getFilterParam(this.searchString),
                applied,
                filters;

            if (_.isUndefined(this.filterComponent())) {
                setTimeout(function () {
                    this.apply();
                }.bind(this), 100);

                return;
            }

            if (!_.isUndefined(this.bookmarks())) {
                if (!_.size(this.bookmarks().getViewData(this.bookmarks().defaultIndex))) {
                    setTimeout(function () {
                        this.apply();
                    }.bind(this), 500);

                    return;
                }
            }

            if (Object.keys(urlFilter).length) {
                applied = this.filterComponent().get('applied');
                filters = $.extend({}, applied, urlFilter);
                this.filterComponent().set('applied', filters);
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
        }
    });
});

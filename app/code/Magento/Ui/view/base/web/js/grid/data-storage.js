/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiClass',
    'es6-collections'
], function ($, _, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            data: {
                indexField: 'entity_id'
            }
        },

        /**
         * Initializes dataStorage configuration.
         *
         * @returns {DataStorage} Chainable.
         */
        initConfig: function () {
            this._super();

            this._requests = new Map();

            return this;
        },

        /**
         * Extracts data which matches specified set of identifiers.
         *
         * @param {Array} ids - Records identifiers.
         * @returns {Array|Boolean}
         */
        getByIds: function (ids) {
            var result = [],
                hasData;

            hasData = ids.every(function (id) {
                var item = this.data[id];

                return item ? result.push(item) : false;
            }, this);

            return hasData ? result : false;
        },

        /**
         * Extracts identifiers of provided records.
         * If no records were provided then full list of
         * current data id's will be returned.
         *
         * @param {Object|Array} [data=this.data]
         * @returns {Array}
         */
        getIds: function (data) {
            data = data || this.data;

            return _.map(data, function (item) {
                return item[this.indexField];
            }, this);
        },

        /**
         *
         * @param {Object} params
         */
        getData: function (params, options) {
            options = options || {};

            if (!options.refresh && this.wasRequestCached(params)) {
                return this.getCachedRequestData(params);
            }

            return this.requestData(params);
        },

        /**
         * Extends records of current data object
         * with the provided records collection.
         *
         * @param {Array} data - An array of records.
         * @returns {DataStorage} Chainable.
         */
        updateData: function (data) {
            var records = _.indexBy(data || [], this.indexField);

            _.extend(this.data, records);

            return this;
        },

        /**
         *
         * @param {Object} params
         * @returns {jQueryPromise}
         */
        requestData: function (params) {
            var handler = this.onRequestComplete.bind(this, params);

            if (this.request && this.request.readyState !== 4) {
                this.request.abort();
            }

            this.request = $.ajax({
                url: this.updateUrl,
                method: 'GET',
                data: params,
                dataType: 'json'
            }).done(handler);

            return this.request;
        },

        /**
         *
         * @param {Object} params
         * @returns {jQueryPromise}
         */
        getCachedRequestData: function (params) {
            var request = this.getCachedRequest(params),
                data    = this.getByIds(request.ids),
                defer   = $.Deferred();

            defer.resolve({
                items: data,
                totalRecords: request.totalRecords
            });

            return defer.promise();
        },

        /**
         * Clears all cached requests.
         *
         * @returns {DataStorage} Chainable.
         */
        clearCachedRequests: function () {
            this._requests.clear();

            return this;
        },

        /**
         *
         * @param {Object} params
         * @returns {Object}
         */
        getCachedRequest: function (params) {
            var request = this.serializeRequest(params);

            return this._requests.get(request);
        },

        /**
         *
         * @param {Object} data
         * @param {Object} params
         * @returns {DataStorage} Chainable.
         */
        cacheRequest: function (data, params) {
            var request = this.serializeRequest(params),
                ids     = this.getIds(data.items);

            this._requests.set(request, {
                ids: ids,
                totalRecords: data.totalRecords
            });

            return this;
        },

        /**
         *
         * @param {Object} params
         * @returns {String}
         */
        serializeRequest: function (params) {
            return JSON.stringify(params);
        },

        /**
         * Checks if request with a specified parameters was already processed.
         *
         * @param {Object} params - Parameters of the request.
         * @returns {Boolean}
         */
        wasRequestCached: function (params) {
            return !!this.getCachedRequest(params);
        },

        /**
         *
         */
        onRequestComplete: function (params, data) {
            this.updateData(data.items);

            if (this.cacheRequests) {
                this.cacheRequest(data, params);
            }
        }
    });
});

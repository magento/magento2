/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiClass'
], function ($, _, utils, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            cacheRequests: true,
            cachedRequestDelay: 50,
            indexField: 'entity_id',
            requestConfig: {
                url: '${ $.updateUrl }',
                method: 'GET',
                dataType: 'json'
            },
            data: {}
        },

        /**
         * Initializes dataStorage configuration.
         *
         * @returns {DataStorage} Chainable.
         */
        initConfig: function () {
            this._super();

            this._requests = [];

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

            return _.pluck(data, this.indexField);
        },

        /**
         * Extracts data which matches specified parameters.
         *
         * @param {Object} params - Request parameters.
         * @param {Object} [options={}]
         * @returns {jQueryPromise}
         */
        getData: function (params, options) {
            var cachedRequest = this.getRequest(params);

            if (params && params.filters && params.filters['store_id']) {
                cachedRequest = false;
            }

            options = options || {};

            return !options.refresh && cachedRequest ?
                this.getRequestData(cachedRequest) :
                this.requestData(params);
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
         * Sends request to the server with provided parameters.
         *
         * @param {Object} params - Request parameters.
         * @returns {jQueryPromise}
         */
        requestData: function (params) {
            var query = utils.copy(params),
                handler = this.onRequestComplete.bind(this, query),
                request;

            this.requestConfig.data = query;
            request = $.ajax(this.requestConfig).done(handler);

            return request;
        },

        /**
         * Returns request's instance which
         * contains provided parameters.
         *
         * @param {Object} params - Request parameters.
         * @returns {Object} Instance of request.
         */
        getRequest: function (params) {
            return _.find(this._requests, function (request) {
                return _.isEqual(params, request.params);
            }, this);
        },

        /**
         * Forms data object associated with provided request.
         *
         * @param {Object} request - Request object.
         * @returns {jQueryPromise}
         */
        getRequestData: function (request) {
            var defer = $.Deferred(),
                resolve = defer.resolve.bind(defer),
                delay = this.cachedRequestDelay,
                result;

            result = {
                items: this.getByIds(request.ids),
                totalRecords: request.totalRecords
            };

            delay ?
                _.delay(resolve, delay, result) :
                resolve(result);

            return defer.promise();
        },

        /**
         * Caches requests object with provdided parameters
         * and data object associated with it.
         *
         * @param {Object} data - Data associated with request.
         * @param {Object} params - Request parameters.
         * @returns {DataStorage} Chainable.
         */
        cacheRequest: function (data, params) {
            var cached = this.getRequest(params);

            if (cached) {
                this.removeRequest(cached);
            }

            this._requests.push({
                ids: this.getIds(data.items),
                params: params,
                totalRecords: data.totalRecords
            });

            return this;
        },

        /**
         * Clears all cached requests.
         *
         * @returns {DataStorage} Chainable.
         */
        clearRequests: function () {
            this._requests.splice(0);

            return this;
        },

        /**
         * Removes provided request object from cached requests list.
         *
         * @param {Object} request - Request object.
         * @returns {DataStorage} Chainable.
         */
        removeRequest: function (request) {
            var requests = this._requests,
                index = requests.indexOf(request);

            if (~index) {
                requests.splice(index, 1);
            }

            return this;
        },

        /**
         * Checks if request with a specified parameters was cached.
         *
         * @param {Object} params - Parameters of the request.
         * @returns {Boolean}
         */
        wasRequested: function (params) {
            return !!this.getRequest(params);
        },

        /**
         * Handles successful data request.
         *
         * @param {Object} params - Request parameters.
         * @param {Object} data - Response data.
         */
        onRequestComplete: function (params, data) {
            this.updateData(data.items);

            if (this.cacheRequests) {
                this.cacheRequest(data, params);
            }
        }
    });
});

/**
 * Copyright © 2015 Magento. All rights reserved.
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
            cachedRequestDelay: 500,
            indexField: 'entity_id',
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

            return _.map(data, function (item) {
                return item[this.indexField];
            }, this);
        },

        /**
         *
         * @param {Object} params
         * @param {Object} [options={}]
         * @returns {jQueryPromise}
         */
        getData: function (params, options) {
            var cachedRequest = this.getCachedRequest(params);

            options = options || {};

            return !options.refresh && cachedRequest ?
                this.getCachedRequestData(cachedRequest) :
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
            var handler = this.onRequestComplete.bind(this, params),
                request;

            request = $.ajax({
                url: this.updateUrl,
                method: 'GET',
                data: params,
                dataType: 'json'
            }).done(handler);

            return request;
        },

        /**
         * Returns request's instance which
         * contains provided parameters.
         *
         * @param {Object} params - Request parameters.
         * @returns {Object} Instance of request.
         */
        getCachedRequest: function (params) {
            return _.find(this._requests, function (request) {
                return _.isEqual(params, request.params);
            }, this);
        },

        /**
         * Forms data object associated with a provided request.
         *
         * @param {Object} request - Request object.
         * @returns {jQueryPromise}
         */
        getCachedRequestData: function (request) {
            var defer   = $.Deferred(),
                resolve = defer.resolve.bind(defer),
                delay   = this.cachedRequestDelay,
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
         *
         * @param {Object} data - Data associated with request.
         * @param {Object} params - Request parameters.
         * @returns {DataStorage} Chainable.
         */
        cacheRequest: function (data, params) {
            var request = {
                ids:            this.getIds(data.items),
                params:         utils.copy(params),
                totalRecords:   data.totalRecords
            };

            this._requests.push(request);

            return this;
        },

        /**
         * Clears all cached requests.
         *
         * @returns {DataStorage} Chainable.
         */
        clearCachedRequests: function () {
            this._requests.splice(0);

            return this;
        },

        /**
         * Removes provided request object from cached requests list.
         *
         * @param {Object} request - Request object.
         * @returns {DataStorage} Chainable.
         */
        removeCachedRequest: function (request) {
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
            return !!this.getCachedRequest(params);
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

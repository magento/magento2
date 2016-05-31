/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'es6-collections'
], function ($, _) {
    'use strict';

    var privateData = new WeakMap();

    /**
     * Extarcts private items storage associated
     * with a provided registry instance.
     *
     * @param {Object} container
     * @returns {Object}
     */
    function getItems(container) {
        return privateData.get(container).items;
    }

    /**
     * Extracts private requests array associated
     * with a provided registry instance.
     *
     * @param {Object} container
     * @returns {Array}
     */
    function getRequests(container) {
        return privateData.get(container).requests;
    }

    /**
     * Wrapper function used for convinient access to the elements.
     * See 'async' method for examples of usage and comparison
     * with a regular 'get' method.
     *
     * @param {(String|Object|Function)} name - Key of the requested element.
     * @param {Registry} registry - Instance of a registry
     *      where to search for the element.
     * @param {(Function|String)} [method] - Optional callback function
     *      or a name of the elements' method which
     *      will be invoked when element is available in registry.
     * @returns {*}
     */
    function async(name, registry, method) {
        var args = _.toArray(arguments).slice(3);

        if (_.isString(method)) {
            registry.get(name, function (component) {
                component[method].apply(component, args);
            });
        } else if (_.isFunction(method)) {
            registry.get(name, method);
        } else if (!args.length) {
            return registry.get(name);
        }
    }

    /**
     * Checks that every property of the query object
     * is present and equal to the corresponding
     * property in target object.
     * Note that non-strict comparison is used.
     *
     * @param {Object} query - Query object.
     * @param {Object} target - Target object.
     * @returns {Boolean}
     */
    function compare(query, target) {
        var matches = true,
            index,
            keys,
            key;

        if (!_.isObject(query) || !_.isObject(target)) {
            return false;
        }

        keys = Object.getOwnPropertyNames(query);
        index = keys.length;

        while (matches && index--) {
            key = keys[index];

            /* eslint-disable eqeqeq */
            if (target[key] != query[key]) {
                matches = false;
            }

            /* eslint-enable eqeqeq */
        }

        return matches;
    }

    /**
     * Explodes incoming string into object if
     * string is defined as a set of key = value pairs.
     *
     * @param {(String|*)} query - String to be processed.
     * @returns {Object|*} Either created object or an unmodified incoming
     *      value if conversion was not possible.
     * @example Sample conversions.
     *      'key = value, key2 = value2'
     *      => {key: 'value', key2: 'value2'}
     */
    function explode(query) {
        var result = {},
            index,
            data;

        if (typeof query !== 'string' || !~query.indexOf('=')) {
            return query;
        }

        query = query.split(',');
        index = query.length;

        while (index--) {
            data = query[index].split('=');

            result[data[0].trim()] = data[1].trim();
        }

        return result;
    }

    /**
     * Extracts items from the provided data object
     * which matches specified search criteria.
     *
     * @param {Object} data - Data object where to perform a lookup.
     * @param {(String|Object|Function)} query - Seach criteria.
     * @param {Boolean} findAll - Flag that defines whether to
     *      search for all applicable items or to stop on a first found entry.
     * @returns {Array|Object|*}
     */
    function find(data, query, findAll) {
        var iterator,
            item;

        query = explode(query);

        if (typeof query === 'string') {
            item = data[query];

            if (findAll) {
                return item ? [item] : [];
            }

            return item;
        }

        iterator = !_.isFunction(query) ?
            compare.bind(null, query) :
            query;

        return findAll ?
            _.filter(data, iterator) :
            _.find(data, iterator);
    }

    /**
     * @constructor
     */
    function Registry() {
        var data = {
            items: {},
            requests: []
        };

        this._updateRequests = _.debounce(this._updateRequests.bind(this), 10);
        privateData.set(this, data);
    }

    Registry.prototype = {
        constructor: Registry,

        /**
         * Retrieves item from registry which matches specified search criteria.
         *
         * @param {(Object|String|Function|Array)} query - Search condition (see examples).
         * @param {Function} [callback] - Callback that will be invoked when
         *      all of the requested items are available.
         * @returns {*}
         *
         * @example Requesting item by it's name.
         *      var obj = {index: 'test', sample: true};
         *
         *      registry.set('first', obj);
         *      registry.get('first') === obj;
         *      => true
         *
         * @example Requesting item with a specific properties.
         *      registry.get('sample = 1, index = test') === obj;
         *      => true
         *      registry.get('sample = 0, index = foo') === obj;
         *      => false
         *
         * @example Declaring search criteria as an object.
         *      registry.get({sample: true}) === obj;
         *      => true;
         *
         * @example Providing custom search handler.
         *      registry.get(function (item) { return item.sample === true; }) === obj;
         *      => true
         *
         * @example Sample asynchronous request declaration.
         *      registry.get('index = test', function (item) {});
         *
         * @example Requesting multiple elements.
         *      registry.set('second', {index: 'test2'});
         *      registry.get(['first', 'second'], function (first, second) {});
         */
        get: function (query, callback) {
            if (typeof callback !== 'function') {
                return find(getItems(this), query);
            }

            this._addRequest(query, callback);
        },

        /**
         * Sets provided item to the registry.
         *
         * @param {String} id - Item's identifier.
         * @param {*} item - Item's data.
         * returns {Registry} Chainable.
         */
        set: function (id, item) {
            getItems(this)[id] = item;

            this._updateRequests();

            return this;
        },

        /**
         * Removes specified item from registry.
         * Note that search query is not applicable.
         *
         * @param {String} id - Item's identifier.
         * @returns {Registry} Chainable.
         */
        remove: function (id) {
            delete getItems(this)[id];

            return this;
        },

        /**
         * Retrieves a collection of elements that match
         * provided search criteria.
         *
         * @param {(Object|String|Function)} query - Search query.
         *      See 'get' method for the syntax examples.
         * @returns {Array} Found elements.
         */
        filter: function (query) {
            return find(getItems(this), query, true);
        },

        /**
         * Checks that at least one element in collection
         * matches provided search criteria.
         *
         * @param {(Object|String|Function)} query - Search query.
         *      See 'get' method for the syntax examples.
         * @returns {Boolean}
         */
        has: function (query) {
            return !!this.get(query);
        },

        /**
         * Checks that registry contains a provided item.
         *
         * @param {*} item - Item to be checked.
         * @returns {Boolean}
         */
        contains: function (item) {
            return _.contains(getItems(this), item);
        },

        /**
         * Extracts identifier of an item if it's present in registry.
         *
         * @param {*} item - Item whose identifier will be extracted.
         * @returns {String|Undefined}
         */
        indexOf: function (item) {
            return _.findKey(getItems(this), function (elem) {
                return item === elem;
            });
        },

        /**
         * Same as a 'get' method except that it returns
         * a promise object instead of invoking provided callback.
         *
         * @param {(String|Function|Object|Array)} query - Search query.
         *      See 'get' method for the syntax examples.
         * @returns {jQueryPromise}
         */
        promise: function (query) {
            var defer    = $.Deferred(),
                callback = defer.resolve.bind(defer);

            this.get(query, callback);

            return defer.promise();
        },

        /**
         * Creates a wrapper function over the provided search query
         * in order to provide somehow more convinient access to the
         * registrie's items.
         *
         * @param {(String|Object|Function)} query - Search criteria.
         *      See 'get' method for the syntax examples.
         * @returns {Function}
         *
         * @example Comparison with a 'get' method on retrieving items.
         *      var module = registry.async('name');
         *
         *      module();
         *      => registry.get('name');
         *
         * @example Asynchronous request.
         *      module(function (component) {});
         *      => registry.get('name', function (component) {});
         *
         * @example Requesting item and invoking it's method with specified parameters.
         *      module('trigger', true);
         *      => registry.get('name', function (component) {
         *          component.trigger(true);
         *      });
         */
        async: function (query) {
            return async.bind(null, query, this);
        },

        /**
         * Creates new instance of a Registry.
         *
         * @returns {Registry} New instance.
         */
        create: function () {
            return new Registry;
        },

        /**
         * Adds new request to the queue or resolves it immediately
         * if all of the required items are available.
         *
         * @private
         * @param {(Object|String|Function|Array)} queries - Search criteria.
         *      See 'get' method for the syntax examples.
         * @param {Function} callback - Callback that will be invoked when
         *      all of the requested items are available.
         * @returns {Registry}
         */
        _addRequest: function (queries, callback) {
            var request;

            if (!Array.isArray(queries)) {
                queries = queries ? [queries] : [];
            }

            request = {
                queries: queries.map(explode),
                callback: callback
            };

            this._canResolve(request) ?
                this._resolveRequest(request) :
                getRequests(this).push(request);

            return this;
        },

        /**
         * Updates requests list resolving applicable items.
         *
         * @private
         * @returns {Registry} Chainable.
         */
        _updateRequests: function () {
            getRequests(this)
                .filter(this._canResolve, this)
                .forEach(this._resolveRequest, this);

            return this;
        },

        /**
         * Resolves provided request invoking it's callback
         * with items specified in query parameters.
         *
         * @private
         * @param {Object} request - Request object.
         * @returns {Registry} Chainable.
         */
        _resolveRequest: function (request) {
            var requests = getRequests(this),
                items    = request.queries.map(this.get, this),
                index    = requests.indexOf(request);

            request.callback.apply(null, items);

            if (~index) {
                requests.splice(index, 1);
            }

            return this;
        },

        /**
         * Checks if provided request can be resolved.
         *
         * @private
         * @param {Object} request - Request object.
         * @returns {Boolean}
         */
        _canResolve: function (request) {
            var queries = request.queries;

            return queries.every(this.has, this);
        }
    };

    return new Registry;
});

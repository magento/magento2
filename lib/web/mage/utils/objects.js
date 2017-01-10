/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore'
], function (ko, $, _) {
    'use strict';

    var primitives = [
        'undefined',
        'boolean',
        'number',
        'string'
    ];

    /**
     * Sets nested property of a specified object.
     * @private
     *
     * @param {Object} parent - Object to look inside for the properties.
     * @param {Array} path - Splitted path the property.
     * @param {*} value - Value of the last property in 'path' array.
     * returns {*} New value for the property.
     */
    function setNested(parent, path, value) {
        var last = path.pop(),
            len = path.length,
            pi = 0,
            part = path[pi];

        for (; pi < len; part = path[++pi]) {
            if (!_.isObject(parent[part])) {
                parent[part] = {};
            }

            parent = parent[part];
        }

        if (typeof parent[last] === 'function') {
            parent[last](value);
        } else {
            parent[last] = value;
        }

        return value;
    }

    /**
     * Retrieves value of a nested property.
     * @private
     *
     * @param {Object} parent - Object to look inside for the properties.
     * @param {Array} path - Splitted path the property.
     * @returns {*} Value of the property.
     */
    function getNested(parent, path) {
        var exists = true,
            len = path.length,
            pi = 0;

        for (; pi < len && exists; pi++) {
            parent = parent[path[pi]];

            if (typeof parent === 'undefined') {
                exists = false;
            }
        }

        if (exists) {
            if (ko.isObservable(parent)) {
                parent = parent();
            }

            return parent;
        }
    }

    /**
     * Removes property from a specified object.
     * @private
     *
     * @param {Object} parent - Object from which to remove property.
     * @param {Array} path - Splitted path to the propery.
     */
    function removeNested(parent, path) {
        var field = path.pop();

        parent = getNested(parent, path);

        if (_.isObject(parent)) {
            delete parent[field];
        }
    }

    return {

        /**
         * Retrieves or defines objects' property by a composite path.
         *
         * @param {Object} data - Container for the properties specified in path.
         * @param {String} path - Objects' properties divided by dots.
         * @param {*} [value] - New value for the last property.
         * @returns {*} Returns value of the last property in chain.
         *
         * @example
         *      utils.nested({}, 'one.two', 3);
         *      => { one: {two: 3} }
         */
        nested: function (data, path, value) {
            var action = arguments.length > 2 ? setNested : getNested;

            path = path ? path.split('.') : [];

            return action(data, path, value);
        },

        /**
         * Removes nested property from an object.
         *
         * @param {Object} data - Data source.
         * @param {String} path - Path to the property e.g. 'one.two.three'
         */
        nestedRemove: function (data, path) {
            path = path.split('.');

            removeNested(data, path);
        },

        /**
         * Flattens objects' nested properties.
         *
         * @param {Object} data - Object to flatten.
         * @param {String} [separator='.'] - Objects' keys separator.
         * @returns {Object} Flattened object.
         *
         * @example Example with a default separator.
         *      utils.flatten({one: { two: { three: 'value'} }});
         *      => { 'one.two.three': 'value' };
         *
         * @example Example with a custom separator.
         *      utils.flatten({one: { two: { three: 'value'} }}, '=>');
         *      => {'one=>two=>three': 'value'};
         */
        flatten: function (data, separator, parent, result) {
            separator = separator || '.';
            result = result || {};

            _.each(data, function (node, name) {
                if (parent) {
                    name = parent + separator + name;
                }

                typeof node === 'object' ?
                    this.flatten(node, separator, name, result) :
                    result[name] = node;

            }, this);

            return result;
        },

        /**
         * Opposite operation of the 'flatten' method.
         *
         * @param {Object} data - Previously flattened object.
         * @param {String} [separator='.'] - Keys separator.
         * @returns {Object} Object with nested properties.
         *
         * @example Example using custom separator.
         *      utils.unflatten({'one=>two': 'value'}, '=>');
         *      => {
         *          one: { two: 'value' }
         *      };
         */
        unflatten: function (data, separator) {
            var result = {};

            separator = separator || '.';

            _.each(data, function (value, nodes) {
                nodes = nodes.split(separator);

                setNested(result, nodes, value);
            });

            return result;
        },

        /**
         * Same operation as 'flatten' method,
         * but returns objects' keys wrapped in '[]'.
         *
         * @param {Object} data - Object that should be serialized.
         * @returns {Object} Serialized data.
         *
         * @example
         *      utils.serialize({one: { two: { three: 'value'} }});
         *      => { 'one[two][three]': 'value' }
         */
        serialize: function (data) {
            var result = {};

            data = this.flatten(data);

            _.each(data, function (value, keys) {
                keys = this.serializeName(keys);
                value = _.isUndefined(value) ? '' : value;

                result[keys] = value;
            }, this);

            return result;
        },

        /**
         * Performs deep extend of specified objects.
         *
         * @returns {Object|Array} Extended object.
         */
        extend: function () {
            var args = _.toArray(arguments);

            args.unshift(true);

            return $.extend.apply($, args);
        },

        /**
         * Performs a deep clone of a specified object.
         *
         * @param {(Object|Array)} data - Data that should be copied.
         * @returns {Object|Array} Cloned object.
         */
        copy: function (data) {
            var result = data,
                isArray = Array.isArray(data),
                placeholder;

            if (this.isObject(data) || isArray) {
                placeholder = isArray ? [] : {};
                result = this.extend(placeholder, data);
            }

            return result;
        },

        /**
         * Performs a deep clone of a specified object.
         * Doesn't save links to original object.
         *
         * @param {*} original - Object to clone
         * @returns {*}
         */
        hardCopy: function (original) {
            if (original === null || typeof original !== 'object') {
                return original;
            }

            return JSON.parse(JSON.stringify(original));
        },

        /**
         * Removes specified nested properties from the target object.
         *
         * @param {Object} target - Object whose properties should be removed.
         * @param {(...String|Array|Object)} list - List that specifies properties to be removed.
         * @returns {Object} Modified object.
         *
         * @example Basic usage
         *      var obj = {a: {b: 2}, c: 'a'};
         *
         *      omit(obj, 'a.b');
         *      => {'a.b': 2};
         *      obj => {a: {}, c: 'a'};
         *
         * @example Various syntaxes that would return same result
         *      omit(obj, ['a.b', 'c']);
         *      omit(obj, 'a.b', 'c');
         *      omit(obj, {'a.b': true, 'c': true});
         */
        omit: function (target, list) {
            var removed = {},
                ignored = list;

            if (this.isObject(list)) {
                ignored = [];

                _.each(list, function (value, key) {
                    if (value) {
                        ignored.push(key);
                    }
                });
            } else if (_.isString(list)) {
                ignored = _.toArray(arguments).slice(1);
            }

            _.each(ignored, function (path) {
                var value = this.nested(target, path);

                if (!_.isUndefined(value)) {
                    removed[path] = value;

                    this.nestedRemove(target, path);
                }
            }, this);

            return removed;
        },

        /**
         * Checks if provided value is a plain object.
         *
         * @param {*} value - Value to be checked.
         * @returns {Boolean}
         */
        isObject: function (value) {
            var objProto = Object.prototype;

            return typeof value == 'object' ?
            objProto.toString.call(value) === '[object Object]' :
                false;
        },

        /**
         *
         * @param {*} value
         * @returns {Boolean}
         */
        isPrimitive: function (value) {
            return value === null || ~primitives.indexOf(typeof value);
        },

        /**
         * Iterates over obj props/array elems recursively, applying action to each one
         *
         * @param {Object|Array} data - Data to be iterated.
         * @param {Function} action - Callback to be called with each item as an argument.
         * @param {Number} [maxDepth=7] - Max recursion depth.
         */
        forEachRecursive: function (data, action, maxDepth) {
            maxDepth = typeof maxDepth === 'number' && !isNaN(maxDepth) ? maxDepth - 1 : 7;

            if (!_.isFunction(action) || _.isFunction(data) || maxDepth < 0) {
                return;
            }

            if (!_.isObject(data)) {
                action(data);

                return;
            }

            _.each(data, function (value) {
                this.forEachRecursive(value, action, maxDepth);
            }, this);

            action(data);
        },

        /**
         * Maps obj props/array elems recursively
         *
         * @param {Object|Array} data - Data to be iterated.
         * @param {Function} action - Callback to transform each item.
         * @param {Number} [maxDepth=7] - Max recursion depth.
         *
         * @returns {Object|Array}
         */
        mapRecursive: function (data, action, maxDepth) {
            var newData;

            maxDepth = typeof maxDepth === 'number' && !isNaN(maxDepth) ? maxDepth - 1 : 7;

            if (!_.isFunction(action) || _.isFunction(data) || maxDepth < 0) {
                return data;
            }

            if (!_.isObject(data)) {
                return action(data);
            }

            if (_.isArray(data)) {
                newData = _.map(data, function (item) {
                    return this.mapRecursive(item, action, maxDepth);
                }, this);

                return action(newData);
            }

            newData = _.mapObject(data, function (val, key) {
                if (data.hasOwnProperty(key)) {
                    return this.mapRecursive(val, action, maxDepth);
                }

                return val;
            }, this);

            return action(newData);
        },

        /**
         * Removes empty(in common sence) obj props/array elems
         *
         * @param {*} data - Data to be cleaned.
         * @returns {*}
         */
        removeEmptyValues: function (data) {
            if (!_.isObject(data)) {
                return data;
            }

            if (_.isArray(data)) {
                return data.filter(function (item) {
                    return !this.isEmptyObj(item);
                }, this);
            }

            return _.omit(data, this.isEmptyObj.bind(this));
        },

        /**
         * Checks that argument of any type is empty in common sence:
         * empty string, string with spaces only, object without own props, empty array, null or undefined
         *
         * @param {*} val - Value to be checked.
         * @returns {Boolean}
         */
        isEmptyObj: function (val) {

            return _.isObject(val) && _.isEmpty(val) ||
            this.isEmpty(val) ||
            val && val.trim && this.isEmpty(val.trim());
        }
    };
});

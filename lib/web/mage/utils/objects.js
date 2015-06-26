/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'underscore'
], function (ko, $, _) {
    'use strict';

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
            var result  = data,
                isArray = Array.isArray(data),
                placeholder;

            if (this.isObject(data) || isArray) {
                placeholder = isArray ? [] : {};
                result      = this.extend(placeholder, data);
            }

            return result;
        },

        /**
         * Removes specified nested properties from the target object.
         *
         * @param {Obeject} target - Object whose properties hsould be removed.
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
                removed[path] = this.nested(target, path);

                this.nestedRemove(target, path);
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
        }
    };
});

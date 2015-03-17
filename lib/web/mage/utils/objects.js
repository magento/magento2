define([
    'jquery',
    'underscore'
], function ($, _) {
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
        var last = path.pop();

        path.forEach(function (part) {
            if (_.isUndefined(parent[part])) {
                parent[part] = {};
            }

            parent = parent[part];
        });

        parent[last] = value;

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
        var exists;

        exists = path.every(function (part) {
            parent = parent[part];

            return !_.isUndefined(parent);
        });

        if (exists) {
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

        extend: function (target) {
            var extenders = _.toArray(arguments).splice(1),
                clone,
                src;

            extenders.forEach(function (node) {
                _.each(node, function (value, key) {
                    src = target[key];

                    if (this.isObject(value) || Array.isArray(value)) {
                        if (Array.isArray(value)) {
                            clone = src && Array.isArray(src) ? src : [];
                        } else {
                            clone = src && this.isObject(src) ? src : {};
                        }

                        target[key] = this.extend(clone, value);
                    } else if (!_.isUndefined(value)) {
                        target[key] = value;
                    }
                }, this);
            }, this);

            return target;
        },

        isObject: function (data) {
            var objProto = Object.prototype;

            return typeof data == 'object' ?
                objProto.toString.call(data) === '[object Object]' :
                false;
        }
    };
});

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
            var args = _.toArray(arguments),
                deepExtend;

            /**
             * Checks if value isn't specific object type
             * -@param (Number) val
             * @returns {Boolean}
             */
            function isSpecificValue(val) {
                return !!(val instanceof Date || val instanceof RegExp);
            }

            /**
             * Clones specific types
             * -@param (Number) val
             * @returns {*}
             */
            function cloneSpecificValue(val) {
                if (val instanceof Date) {
                    return new Date(val.getTime());
                } else if (val instanceof RegExp) {
                    return new RegExp(val);
                } else {
                    throw new Error('Unexpected situation');
                }
            }

            /**
             * Erases duplication in array
             * -@param {Array} a
             * @returns {Array}
             */
            function arrayNoDuplication(a) {
                var temp = {},
                    r = [],
                    i = 0,
                    k;

                for (i = 0; i < a.length; i++) {
                    temp[a[i]] = true;
                }
                r = [];

                for (k in temp) {
                    r.push(k);
                }

                return r;
            }

            /**
             * Recursive cloning array.
             */
            function deepCloneArray(arr) {
                var clone = [];
                arr.forEach(function (item, index) {
                    if (typeof item === 'object' && item !== null) {
                        if (Array.isArray(item)) {
                            clone[index] = deepCloneArray(item);
                        } else if (isSpecificValue(item)) {
                            clone[index] = cloneSpecificValue(item);
                        } else {
                            clone[index] = deepExtend({}, item);
                        }
                    } else {
                        clone[index] = item;
                    }
                });

                return clone;
            }

            /**
             * Extening object that entered in first argument.
             *
             * Returns extended object or false if have no target object or incorrect type.
             *
             * If you wish to clone source object (without modify it), just use empty new
             * object as first argument, like this:
             *   deepExtend({}, yourObj_1, [yourObj_N]);
             */
            deepExtend = function (/*obj_1, [obj_2], [obj_N]*/) {
                var target,
                    args,
                    val,
                    src,
                    clone;

                if (arguments.length < 1 || typeof arguments[0] !== 'object') {
                    return false;
                }

                if (arguments.length < 2) {
                    return arguments[0];
                }

                target = arguments[0];

                // convert arguments to array and cut off target object
                args = Array.prototype.slice.call(arguments, 1);

                args.forEach(function (obj) {
                    // skip argument if it is array or isn't object
                    if (typeof obj !== 'object' || Array.isArray(obj)) {
                        return;
                    }

                    Object.keys(obj).forEach(function (key) {
                        src = target[key]; // source value
                        val = obj[key]; // new value

                        // recursion prevention
                        if (val === target) {
                            return;

                            /**
                             * if new value isn't object then just overwrite by new value
                             * instead of extending.
                             */
                        } else if (typeof val !== 'object' || val === null) {
                            target[key] = val;

                            return;

                            // just clone arrays (and recursive clone objects inside)
                        } else if (Array.isArray(val)) {

                            if (Array.isArray(target[key])) {
                                target[key] = arrayNoDuplication(target[key].concat(val));

                                return;
                            }

                            target[key] = deepCloneArray(val);

                            return;

                            // custom cloning and overwrite for specific objects
                        } else if (isSpecificValue(val)) {
                            target[key] = cloneSpecificValue(val);

                            return;

                            // overwrite by new value if source isn't object or array
                        } else if (typeof src !== 'object' || src === null || Array.isArray(src)) {
                            target[key] = deepExtend({}, val);

                            return;

                            // source value and new value is objects both, extending...
                        } else {
                            target[key] = deepExtend(src, val);

                            return;
                        }
                    });
                });

                return target;
            };

            return deepExtend(args);
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
        }
    };
});

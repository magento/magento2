/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils/objects'
], function (_, utils) {
    'use strict';

    var result = [];

    /**
     * Checks if all of the provided arrays contains equal values.
     *
     * @param {(Boolean|Array)} [keepOrder=false]
     * @param {Array} target
     * @returns {Boolean}
     */
    function equalArrays(keepOrder, target) {
        var args = _.toArray(arguments),
            arrays;

        if (!Array.isArray(keepOrder)) {
            arrays      = args.slice(2);
        } else {
            target      = keepOrder;
            keepOrder   = false;
            arrays      = args.slice(1);
        }

        if (!arrays.length) {
            return true;
        }

        return arrays.every(function (array) {
            if (array === target) {
                return true;
            } else if (array.length !== target.length) {
                return false;
            } else if (!keepOrder) {
                return !_.difference(target, array).length;
            }

            return array.every(function (value, index) {
                return target.indexOf(value) === index;
            });
        });
    }

    /**
     * Checks if two values are different.
     *
     * @param {*} a - First value.
     * @param {*} b - Second value.
     * @returns {Boolean}
     */
    function isDifferent(a, b) {
        var oldIsPrimitive = utils.isPrimitive(a);

        if (Array.isArray(a) && Array.isArray(b)) {
            return !equalArrays(true, a, b);
        }

        return oldIsPrimitive ? a !== b : true;
    }

    /**
     * @param {String} prefix
     * @param {String} part
     */
    function getPath(prefix, part) {
        return prefix ? prefix + '.' + part : part;
    }

    /**
     * Checks if object has own specified property.
     *
     * @param {*} obj - Value to be checked.
     * @param {String} key - Key of the property.
     * @returns {Boolean}
     */
    function hasOwn(obj, key) {
        return Object.prototype.hasOwnProperty.call(obj, key);
    }

    /**
     * @param {Array} changes
     */
    function getContainers(changes) {
        var containers  = {},
            indexed     = _.indexBy(changes, 'path');

        _.each(indexed, function (change, name) {
            var path;

            name.split('.').forEach(function (part) {
                path = getPath(path, part);

                if (path in indexed) {
                    return;
                }

                (containers[path] = containers[path] || []).push(change);
            });
        });

        return containers;
    }

    /**
     * @param {String} path
     * @param {String} name
     * @param {String} type
     * @param {String} newValue
     * @param {String} oldValue
     */
    function addChange(path, name, type, newValue, oldValue) {
        var data;

        data = {
            path: path,
            name: name,
            type: type
        };

        if (type !== 'remove') {
            data.value = newValue;
            data.oldValue = oldValue;
        } else {
            data.oldValue = newValue;
        }

        result.push(data);
    }

    /**
     * @param {String} ns
     * @param {String} name
     * @param {String} type
     * @param {String} iterator
     * @param {String} placeholder
     */
    function setAll(ns, name, type, iterator, placeholder) {
        var key;

        if (arguments.length > 4) {
            type === 'add' ?
                addChange(ns, name, 'update', iterator, placeholder) :
                addChange(ns, name, 'update', placeholder, iterator);
        } else {
            addChange(ns, name, type, iterator);
        }

        if (!utils.isObject(iterator)) {
            return;
        }

        for (key in iterator) {
            if (hasOwn(iterator, key)) {
                setAll(getPath(ns, key), key, type, iterator[key]);
            }
        }
    }

    /*eslint-disable max-depth*/
    /**
     * @param {Object} old
     * @param {Object} current
     * @param {String} ns
     * @param {String} name
     */
    function compare(old, current, ns, name) {
        var key,
            oldIsObj = utils.isObject(old),
            newIsObj = utils.isObject(current);

        if (oldIsObj && newIsObj) {
            for (key in old) {
                if (hasOwn(old, key) && !hasOwn(current, key)) {
                    setAll(getPath(ns, key), key, 'remove', old[key]);
                }
            }

            for (key in current) {
                if (hasOwn(current, key)) {
                    hasOwn(old, key) ?
                        compare(old[key], current[key], getPath(ns, key), key) :
                        setAll(getPath(ns, key), key, 'add', current[key]);
                }
            }
        } else if (oldIsObj) {
            setAll(ns, name, 'remove', old, current);
        } else if (newIsObj) {
            setAll(ns, name, 'add', current, old);
        } else if (isDifferent(old, current)) {
            addChange(ns, name, 'update', current, old);
        }
    }

    /*eslint-enable max-depth*/

    return {

        /**
         *
         * @returns {Object}
         */
        compare: function () {
            var changes;

            compare.apply(null, arguments);

            changes = result.splice(0);

            return {
                containers: getContainers(changes),
                changes: changes,
                equal: !changes.length
            };
        },

        equalArrays: equalArrays
    };
});

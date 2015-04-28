/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils/objects'
], function (_, utils) {
    'use strict';

    var primitives = [
        'undefined',
        'boolean',
        'number',
        'string'
    ];

    function isDifferent(a, b) {
        var oldIsPrimitive = a === null || ~primitives.indexOf(typeof a);

        return oldIsPrimitive ? a !== b : true;
    }

    function fullPath(prefix, part) {
        return prefix ? prefix + '.' + part : part;
    }

    function format(name, newValue, oldValue, type) {
        return {
            name: name,
            type: type,
            value: newValue,
            oldValue: oldValue
        };
    }

    function flatten(obj, ns, result) {
        var key,
            value;

        result = result || {};
        ns = ns || '';

        if (!utils.isObject(obj)) {
            return result;
        }

        for (key in obj) {
            value = obj[key];

            if (typeof value !== 'function') {
                key = fullPath(ns, key);

                if (utils.isObject(value)) {
                    flatten(value, key, result);
                }

                result[key] = value;
            }
        }

        return result;
    }

    function getConatiners(changes) {
        var result = {},
            indexed;

        indexed = _.indexBy(changes, 'name');

        _.each(indexed, function (change, name) {
            var path;

            name = name.split('.');

            name.forEach(function (part) {
                path = fullPath(path, part);

                if (!(path in indexed)) {
                    result[path] = result[path] || [];
                    result[path].push(change);
                }
            });
        });

        return result;
    }

    function compare(oldObj, newObj, ns) {
        var result,
            data,
            key,
            previous,
            current,
            hasPrimitive;

        result = [];

        oldObj = flatten(oldObj, ns);
        newObj = flatten(newObj, ns);

        /**
         * Define which properties was removed.
         */
        for (key in oldObj) {
            if (!(key in newObj)) {
                data = format(key, undefined, oldObj[key], 'remove');

                result.push(data);
            }
        }

        /**
         * Define added or updated properties.
         */
        for (key in newObj) {
            data = false;
            current = newObj[key];

            if (key in oldObj) {
                previous = oldObj[key];
                hasPrimitive = !utils.isObject(previous) || !utils.isObject(current);

                if (hasPrimitive && isDifferent(previous, current)) {
                    data = format(key, current, previous, 'update');
                }
            } else {
                data = format(key, current, undefined, 'add');
            }

            if (data) {
                result.push(data);
            }
        }

        return result;
    }

    return {
        compare: function (oldValue, newValue, ns) {
            var changes = [];

            if (!_.isFunction(oldValue) && !_.isFunction(newValue)) {
                if (utils.isObject(oldValue) || utils.isObject(newValue)) {
                    changes = compare.apply(null, arguments);
                } else if (isDifferent(oldValue, newValue)) {
                    changes.push(format(ns, newValue, oldValue, 'update'));
                }
            }

            return {
                containers: getConatiners(changes),
                changes: changes,
                equal: !changes.length
            };
        }
    };
});

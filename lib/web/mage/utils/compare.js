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
        result = result || {};
        ns = ns || '';

        if (!utils.isObject(obj)) {
            obj = {};
        }

        _.each(obj, function (value, key) {
            key = fullPath(ns, key);

            if (utils.isObject(value)) {
                flatten(value, key, result);
            }

            result[key] = value;
        });

        return result;
    }

    function getConatiners(changes) {
        var indexed,
            result = {};

        indexed = _.indexBy(changes, 'name');

        _.each(indexed, function (change, name) {
            var path;

            name = name.split('.');

            name.forEach(function (part) {
                path = fullPath(path, part);

                if (!_.has(indexed, path)) {
                    result[path] = result[path] || [];
                    result[path].push(change);
                }
            });
        });

        return result;
    }

    function getModified(oldValues, current, key) {
        var previous,
            someIsObject;

        if (_.has(oldValues, key)) {
            previous = oldValues[key];
            someIsObject = !utils.isObject(previous) || !utils.isObject(current);

            if (someIsObject && isDifferent(previous, current)) {
                return format(key, current, previous, 'update');
            }
        } else {
            return format(key, current, undefined, 'add');
        }
    }

    function getRemoved(newValues, previous, key) {
        if (!_.has(newValues, key)) {
            return format(key, undefined, previous, 'remove');
        }
    }

    function compare(oldObj, newObj, ns) {
        var removed,
            modfied;

        oldObj = flatten(oldObj, ns);
        newObj = flatten(newObj, ns);

        removed = _.map(oldObj, getRemoved.bind(null, newObj)),
        modfied = _.map(newObj, getModified.bind(null, oldObj));

        return _.compact(Array.prototype.concat(removed, modfied));
    }

    return {
        compare: function (oldValue, newValue, ns) {
            var changes = [];

            if (utils.isObject(oldValue) || utils.isObject(newValue)) {
                changes = compare.apply(null, arguments);
            } else if (isDifferent(oldValue, newValue)) {
                changes.push(format(ns, newValue, oldValue, 'update'));
            }

            return {
                containers: getConatiners(changes),
                changes: changes
            };
        }
    };
});

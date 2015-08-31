/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils/objects'
], function (_, utils) {
    'use strict';

    var result = [];

    /**
     * Is equal elements in array depending on order
     *
     * @param {Boolean|Array} keepOrder - (Optional), by default order don't matters
     * @param {Array} target - target array element
     * @returns {*}
     */
    function equalArrays(keepOrder, target) {
        var args = _.toArray(arguments),
            arrays;

        if (!Array.isArray(keepOrder)) {
            arrays = args.slice(2);
        } else {
            target = keepOrder;
            keepOrder = false;
            arrays = args.slice(1);
        }

        if (!arrays.length) {
            return true;
        }

        return arrays.every(function (array) {
            if (array.length !== target.length) {
                return false;
            }

            return array.every(function (value, index) {
                var targetIndex = target.indexOf(value);

                return !keepOrder ? !!~targetIndex : targetIndex === index;
            });
        });
    }

    function isDifferent(a, b) {
        var oldIsPrimitive = utils.isPrimitive(a);

        if (Array.isArray(a) && Array.isArray(b)) {
            return !equalArrays(true, a, b);
        }

        return oldIsPrimitive ? a !== b : true;
    }

    function getPath(prefix, part) {
        return prefix ? prefix + '.' + part : part;
    }

    function getContainers(changes) {
        var result = {},
            indexed;

        indexed = _.indexBy(changes, 'path');

        _.each(indexed, function (change, name) {
            var path;

            name = name.split('.');

            name.forEach(function (part) {
                path = getPath(path, part);

                if (!(path in indexed)) {
                    result[path] = result[path] || [];
                    result[path].push(change);
                }
            });
        });

        return result;
    }

    function addChange(path, name, type, newValue, oldValue) {
        var data = {
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
            setAll(getPath(ns, key), key, type, iterator[key]);
        }
    }

    function compare(old, current, ns, name) {
        var key,
            oldIsObj = utils.isObject(old),
            newIsObj = utils.isObject(current);

        if (oldIsObj && newIsObj) {
            for (key in old) {
                if (!(key in current)) {
                    setAll(getPath(ns, key), key, 'remove', old[key]);
                }
            }

            for (key in current) {
                key in old ?
                    compare(old[key], current[key], getPath(ns, key), key) :
                    setAll(getPath(ns, key), key, 'add', current[key]);
            }
        } else if (oldIsObj) {
            setAll(ns, name, 'remove', old, current);
        } else if (newIsObj) {
            setAll(ns, name, 'add', current, old);
        } else if (isDifferent(old, current)) {
            addChange(ns, name, 'update', current, old);
        }
    }

    return {
        compare: function () {
            var changes;

            compare.apply(null, arguments);

            changes = result.splice(0, result.length);

            return {
                containers: getContainers(changes),
                changes: changes,
                equal: !changes.length
            };
        },

        equalArrays: equalArrays
    };
});
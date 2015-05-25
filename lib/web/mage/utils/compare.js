/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var objType = '[object Object]',
        objString = Object.prototype.toString,
        result = [],
        primitives;

    primitives = [
        'undefined',
        'boolean',
        'number',
        'string'
    ];

    function isDifferent(a, b) {
        var oldIsPrimitive = a === null || ~primitives.indexOf(typeof a);

        return oldIsPrimitive ? a !== b : true;
    }

    function isObject(data) {
        return typeof data == 'object' ?
            objString.call(data) === objType :
            false;
    }

    function getPath(prefix, part) {
        return prefix ? prefix + '.' + part : part;
    }

    function getConatiners(changes) {
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
        var data  = {
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

        if (!isObject(iterator)) {
            return;
        }

        for (key in iterator) {
            setAll(getPath(ns, key), key, type, iterator[key]);
        }
    }

    function compare(old, current, ns, name) {
        var key,
            oldIsObj = isObject(old),
            newIsObj = isObject(current);

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
                containers: getConatiners(changes),
                changes: changes,
                equal: !changes.length
            };
        }
    };
});

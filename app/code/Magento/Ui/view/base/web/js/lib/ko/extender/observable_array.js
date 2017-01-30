/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    function iterator(callback, args, elem) {
        callback = elem[callback];

        if (_.isFunction(callback)) {
            return callback.apply(elem, args);
        }

        return callback;
    }

    function wrapper(method) {
        return function (iteratee) {
            var callback = iteratee,
                elems = this(),
                args = _.toArray(arguments);

            if (_.isString(iteratee)) {
                callback = iterator.bind(null, iteratee, args.slice(1));

                args.unshift(callback);
            }

            args.unshift(elems);

            return _[method].apply(_, args);
        };
    }

    _.extend(ko.observableArray.fn, {
        each: wrapper('each'),

        map: wrapper('map'),

        filter: wrapper('filter'),

        some: wrapper('some'),

        every: wrapper('every'),

        groupBy: wrapper('groupBy'),

        sortBy: wrapper('sortBy'),

        findWhere: function (properties) {
            return _.findWhere(this(), properties);
        },

        contains: function (value) {
            return _.contains(this(), value);
        },

        hasNo: function () {
            return !this.contains.apply(this, arguments);
        },

        getLength: function () {
            return this().length;
        },

        indexBy: function (key) {
            return _.indexBy(this(), key);
        },

        without: function () {
            var args = Array.prototype.slice.call(arguments);

            args.unshift(this());

            return _.without.apply(_, args);
        },

        first: function () {
            return _.first(this());
        },

        last: function () {
            return _.last(this());
        },

        pluck: function () {
            var args = Array.prototype.slice.call(arguments);

            args.unshift(this());

            return _.pluck.apply(_, args);
        }
    });
});

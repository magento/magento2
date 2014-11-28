/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    function observeKeys(keys, array) {
        array.each(function (item) {
            keys.forEach(function (key) {
                item[key] = !ko.isObservable(item[key]) ? ko.observable(item[key]) : item[key];
            });
        });
    }

    _.extend(ko.observableArray.fn, {
        contains: function (value) {
            return _.contains(this(), value);
        },

        hasNo: function (value) {
            return !this.contains.apply(this, arguments);
        },

        observe: function (keys) {
            keys = _.isArray(keys) ? keys : Array.prototype.slice.call(arguments);

            observeKeys(keys, this);
            this.subscribe(observeKeys.bind(this, keys));
        },

        getLength: function () {
            return this().length;
        },

        indexBy: function (key) {
            return _.indexBy(this(), key);
        },

        each: function (iterator, ctx) {
            return _.each(this(), iterator, ctx);
        },

        map: function (iterator, ctx) {
            return _.map(this(), iterator, ctx);
        },

        filter: function (iterator, ctx) {
            return _.filter(this(), iterator, ctx);
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

        some: function (predicate, ctx) {
            return _.some(this(), predicate, ctx);
        },

        every: function (predicate, ctx) {
            return _.every(this(), predicate, ctx);
        },

        groupBy: function (iteratee, ctx) {
            return _.groupBy(this(), iteratee, ctx);
        },

        sortBy: function (iteratee, ctx) {
            return _.sortBy(this(), iteratee, ctx);
        },

        pluck: function(){
            var args = Array.prototype.slice.call(arguments);

            args.unshift(this());

            return _.pluck.apply(_, args);
        }
    });
});
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
    'jquery',
    './events'
], function ($, EventBus) {
    'use strict';

    var events = {};

    function isResolved(promise) {
        return promise.state() === 'resolved';
    };

    function toArray(obj, from) {
        return Array.prototype.slice.call(obj, from || 0);
    };

    function on(context, name, callback) {
        return EventBus.on.call(context, name, callback);
    };

    function trigger(name) {
        return EventBus.trigger.apply(this, toArray(arguments));
    };

    function getStorage(name) {
        return events[name] || {};
    };

    function getCallbacks(name) {
        var storage = getStorage(name);
        return storage.callbacks || [];
    };

    return {
        when: function (name, callback) {
            var storage   = events[name]      = getStorage(name),
                callbacks = storage.callbacks = getCallbacks(name),
                promise   = storage.promise   = storage.promise || $.Deferred(),
                args      = toArray(arguments),
                resolveArgs;

            if (isResolved(promise)) {
                return on(this, name, callback);
            }

            if (~!callbacks.indexOf(callback)) {
                callbacks.push(callback);
            }

            promise.done(function () {

                callback.apply(this, arguments);
                on(this, name, callback);

            }.bind(this));

            return this;
        },

        resolve: function (name) {
            var args    = toArray(arguments, 1),
                storage = events[name]    = getStorage(name),
                promise = storage.promise = storage.promise || $.Deferred();

            if (isResolved(promise)) {
                return trigger.bind(this, name).apply(this, args);
            }

            promise.resolve.apply(promise, args);

            return this;
        }
    }
});
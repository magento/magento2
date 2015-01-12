/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
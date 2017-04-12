/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'es6-collections'
], function (ko, _) {
    'use strict';

    var eventsMap = new WeakMap();

    /**
     * Returns events map or a specific event
     * data associated with a provided object.
     *
     * @param {Object} obj - Key in the events weakmap.
     * @param {String} [name] - Name of the event.
     * @returns {Map|Array|Boolean}
     */
    function getEvents(obj, name) {
        var events = eventsMap.get(obj);

        if (!events) {
            return false;
        }

        return name ? events.get(name) : events;
    }

    /**
     * Adds new event handler.
     *
     * @param {Object} obj - Key in the events weakmap.
     * @param {String} ns - Callback namespace.
     * @param {Fucntion} callback - Event callback.
     * @param {String} name - Name of the event.
     */
    function addHandler(obj, ns, callback, name) {
        var events      = getEvents(obj),
            observable,
            data;

        observable = !ko.isObservable(obj[name]) ?
            ko.getObservable(obj, name) :
            obj[name];

        if (observable) {
            observable.subscribe(callback);

            return;
        }

        if (!events) {
            events = new Map();

            eventsMap.set(obj, events);
        }

        data = {
            callback: callback,
            ns: ns
        };

        events.has(name) ?
            events.get(name).push(data) :
            events.set(name, [data]);
    }

    /**
     * Invokes provided callbacks with a specified arguments.
     *
     * @param {Array} handlers
     * @param {Array} args
     * @returns {Boolean}
     */
    function trigger(handlers, args) {
        var bubble = true,
            callback;

        handlers.forEach(function (handler) {
            callback = handler.callback;

            if (callback.apply(null, args) === false) {
                bubble = false;
            }
        });

        return bubble;
    }

    return {

        /**
         * Calls callback when name event is triggered.
         * @param  {String}   events
         * @param  {Function} callback
         * @return {Object} reference to this
         */
        on: function (events, callback, ns) {
            var iterator;

            if (arguments.length < 2) {
                ns = callback;
            }

            iterator = addHandler.bind(null, this, ns);

            _.isObject(events) ?
                _.each(events, iterator) :
                iterator(callback, events);

            return this;
        },

        /**
         * Removed callback from listening to target event
         * @param  {String} ns
         * @return {Object} reference to this
         */
        off: function (ns) {
            var storage = getEvents(this);

            if (!storage) {
                return this;
            }

            storage.forEach(function (handlers, name) {
                handlers = handlers.filter(function (handler) {
                    return !ns ? false : handler.ns !== ns;
                });

                handlers.length ?
                    storage.set(name, handlers) :
                    storage.delete(name);
            });

            return this;
        },

        /**
         * Triggers event and executes all attached callbacks.
         *
         * @param {String} name - Name of the event to be triggered.
         * @returns {Boolean}
         */
        trigger: function (name) {
            var handlers,
                args;

            handlers = getEvents(this, name),
            args = _.toArray(arguments).slice(1);

            if (!handlers || !name) {
                return true;
            }

            return trigger(handlers, args);
        }
    };
});

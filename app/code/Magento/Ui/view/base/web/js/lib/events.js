/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function(_) {
    'use strict';

    function addHandler(events, ns, callback, name) {
        (events[name] = events[name] || []).push({
            callback: callback,
            ns: ns
        });
    }

    function getEvents(obj, name) {
        var events = obj._events = obj._events || {};

        return name ? events[name] : events;
    }

    function keepHandler(ns, handler){
        if(!ns){
            return false;
        }

        return handler.ns !== ns;
    }

    function trigger(handlers, args){
        var bubble  = true,
            callback;

        handlers.forEach(function(handler){
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
         * @param  {String}   name
         * @param  {Function} callback
         * @return {Object} reference to this
         */
        on: function(events, callback, ns) {
            var storage = getEvents(this),
                iterator; 

            if( arguments.length < 2 ){
                ns = callback;
            }

            iterator = addHandler.bind(null, storage, ns);

            _.isObject(events) ? 
                _.each(events, iterator) :
                iterator(callback, events);

            return this;
        },

        /**
         * Removed callback from listening to target event 
         * @param  {String} name
         * @return {Object} reference to this
         */
        off: function(ns) {
            var storage = getEvents(this),
                filter  = keepHandler.bind(null, ns);

            _.each(storage, function(handlers, name){
                handlers = handlers.filter(filter);

                handlers.length ? 
                    (storage[name] = handlers) :
                    (delete storage[name]);
            });

            return this;
        },

        /**
         * Triggers event and executes all attached callbacks
         * @param  {String} name
         * @return {Object} reference to this
         */
        trigger: function(name) {
            var handlers = getEvents(this, name),
                args     = _.toArray(arguments).slice(1);

            if (_.isUndefined(handlers)) {
                return true;
            }  

            return trigger(handlers, args);
        }
    }
});
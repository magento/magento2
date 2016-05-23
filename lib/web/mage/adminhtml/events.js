/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/modal/alert',
    'prototype'
], function(alert){

    // from http://www.someelement.com/2007/03/eventpublisher-custom-events-la-pubsub.html
    varienEvents = Class.create();

    varienEvents.prototype = {
        initialize: function() {
            this.arrEvents = {};
            this.eventPrefix = '';
        },

        /**
        * Attaches a {handler} function to the publisher's {eventName} event for execution upon the event firing
        * @param {String} eventName
        * @param {Function} handler
        * @param {Boolean} asynchFlag [optional] Defaults to false if omitted. Indicates whether to execute {handler} asynchronously (true) or not (false).
        */
        attachEventHandler : function(eventName, handler) {
            if ((typeof handler == 'undefined') || (handler == null)) {
                return;
            }
            eventName = eventName + this.eventPrefix;
            // using an event cache array to track all handlers for proper cleanup
            if (this.arrEvents[eventName] == null){
                this.arrEvents[eventName] = [];
            }
            //create a custom object containing the handler method and the asynch flag
            var asynchVar = arguments.length > 2 ? arguments[2] : false;
            var handlerObj = {
                method: handler,
                asynch: asynchVar
            };
            this.arrEvents[eventName].push(handlerObj);
        },

        /**
        * Removes a single handler from a specific event
        * @param {String} eventName The event name to clear the handler from
        * @param {Function} handler A reference to the handler function to un-register from the event
        */
        removeEventHandler : function(eventName, handler) {
            eventName = eventName + this.eventPrefix;
            if (this.arrEvents[eventName] != null){
                this.arrEvents[eventName] = this.arrEvents[eventName].reject(function(obj) { return obj.method == handler; });
            }
        },

        /**
        * Removes all handlers from a single event
        * @param {String} eventName The event name to clear handlers from
        */
        clearEventHandlers : function(eventName) {
            eventName = eventName + this.eventPrefix;
            this.arrEvents[eventName] = null;
        },

        /**
        * Removes all handlers from ALL events
        */
        clearAllEventHandlers : function() {
            this.arrEvents = {};
        },

        /**
        * Fires the event {eventName}, resulting in all registered handlers to be executed.
        * It also collects and returns results of all non-asynchronous handlers
        * @param {String} eventName The name of the event to fire
        * @params {Object} args [optional] Any object, will be passed into the handler function as the only argument
        * @return {Array}
        */
        fireEvent : function(eventName) {
            var evtName = eventName + this.eventPrefix;
            var results = [];
            var result;
            if (this.arrEvents[evtName] != null) {
                var len = this.arrEvents[evtName].length; //optimization
                for (var i = 0; i < len; i++) {
                    try {
                        if (arguments.length > 1) {
                            if (this.arrEvents[evtName][i].asynch) {
                                var eventArgs = arguments[1];
                                var method = this.arrEvents[evtName][i].method.bind(this);
                                setTimeout(function() { method(eventArgs) }.bind(this), 10);
                            }
                            else{
                                result = this.arrEvents[evtName][i].method(arguments[1]);
                            }
                        }
                        else {
                            if (this.arrEvents[evtName][i].asynch) {
                                var eventHandler = this.arrEvents[evtName][i].method;
                                setTimeout(eventHandler, 1);
                            }
                            else if (this.arrEvents && this.arrEvents[evtName] && this.arrEvents[evtName][i] && this.arrEvents[evtName][i].method){
                                result = this.arrEvents[evtName][i].method();
                            }
                        }
                        results.push(result);
                    }
                    catch (e) {
                        if (this.id){
                            alert({
                                content: "error: error in " + this.id + ".fireEvent():\n\nevent name: " + eventName + "\n\nerror message: " + e.message
                            });
                        }
                        else {
                            alert({
                                content: "error: error in [unknown object].fireEvent():\n\nevent name: " + eventName + "\n\nerror message: " + e.message
                            });
                        }
                    }
                }
            }
            return results;
        }
    };

    varienGlobalEvents = new varienEvents();

});
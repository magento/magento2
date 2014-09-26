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
    'underscore'
], function(_) {
    'use strict';

    function addHandler(events, callback, name) {
        (events[name] = events[name] || []).push(callback);
    }

    function getEvents(obj, name) {
        var events = obj._events = obj._events || {};

        return name ? events[name] : events;
    }

    return {
        /**
         * Calls callback when name event is triggered.
         * @param  {String}   name
         * @param  {Function} callback
         * @return {Object} reference to this
         */
        on: function(name, callback) {
            var events = getEvents(this);

            typeof name === 'object' ?
                _.each(name, addHandler.bind(window, events)) :
                addHandler(events, callback, name);

            return this;
        },

        /**
         * Removed callback from listening to target event 
         * @param  {String} name
         * @return {Object} reference to this
         */
        off: function(name) {
            var events      = getEvents(this),
                handlers    = events[name];

            if (Array.isArray(handlers)) {
                delete events[name];
            }

            return this;
        },

        /**
         * Triggers event and executes all attached callbacks
         * @param  {String} name
         * @return {Object} reference to this
         */
        trigger: function(name) {
            var handlers = getEvents(this, name),
                args;

            if (typeof handlers !== 'undefined') {
                args = Array.prototype.slice.call(arguments, 1);

                handlers.forEach(function(callback) {
                    callback.apply(this, args);
                });
            }

            return this;
        }
    }
});
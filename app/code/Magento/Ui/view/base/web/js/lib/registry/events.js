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
    'underscore',
    'mage/utils'
], function(_, utils) {
    'use strict';

    function Events(storage){
        this.id = 0,
        
        this.requests   = {};
        this.map        = {};
        this.storage    = storage;

        _.bindAll(this, '_resolve', '_clear');
    }

    Events.prototype = {
        constructor: Events,

        /**
         * Tries to resolve dependencies affected by the scpecified element.
         * @param {String} elem - Elements' name.
         * @returns {events} Chainable.
         */
        resolve: function(elem) {
            var pending = this.map[elem];

            if (Array.isArray(pending)) {
                pending
                    .filter(this._resolve)
                    .forEach(this._clear);
            }

            return this;
        },


        /**
         * Creates a new request for the specified set
                of elements in case some of them wasn't registered yeat.
                Otherwise triggers callback immediately.
         * @param {Array} elems - Requested elements.
         * @param {Function} callback -
                Callback that will be triggered as soon as
                all of the elements will be registered. 
         * @returns {events} Chainable.
         */
        wait: function(elems, callback) {
            var storage = this.storage,
                map     = this.map;

            if (storage.has(elems)) {
                return callback.apply(null, storage.get(elems));
            }

            elems.forEach(function(elem) {
                (map[elem] = map[elem] || []).push(this.id);
            }, this);

            this.requests[this.id++] = {
                callback:   callback,
                deps:       elems
            };

            return this;
        },

        /**
         * Tries to resolve pending request.
         * @private
         * @param {Number} id - Id of request.
         * @returns {Boolean} Whether specified request was successfully resolved.
         */
        _resolve: function(id) {
            var request     = this.requests[id],
                elems       = request.deps,
                storage     = this.storage,
                isResolved;

            isResolved = storage.has(elems);

            if (isResolved) {
                request.callback.apply(null, storage.get(elems));
            }

            return isResolved;
        },

        /**
         * Clears all of the entries of a specified request.
         * @private
         * @param {Number} id - Id of request.
         */
        _clear: function(id) {
            var map     = this.map,
                elems   = this.requests[id].deps;

            elems.forEach(function(elem){
                utils.remove(map[elem], id);
            });

            delete this.requests[id];
        }
    };

    return Events;
});


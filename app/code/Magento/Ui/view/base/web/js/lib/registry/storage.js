/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function(){
    'use strict';
    
    function Storage(){
        this.data = {};
    }

    Storage.prototype = {
        constructor: Storage,

        /**
         * Retrieves values of the specified elements.
         *
         * @param {Array} elems - An array of elements.
         * @returns {Array} Array of values. 
         */
        get: function(elems) {
            var data = this.data,
                record;

            elems = elems || [];

            return elems.map(function(elem) {
                record = data[elem];

                return record ? record.value : undefined;
            });
        },


        /**
         * Sets key -> value pair.
         *
         * @param {String} elem - Elements' name.
         * @param {*} value - Value of the element.
         * returns {storage} Chainable.
         */
        set: function(elem, value) {
            var data    = this.data,
                record  = data[elem] = data[elem] || {};

            record.value = value;

            return this;
        },


        /**
         * Removes specified elements from storage.
         *
         * @param {Array} elems - An array of elements to be removed.
         * returns {storage} Chainable.
         */
        remove: function(elems) {
            var data = this.data;

            elems.forEach(function(elem) {
                delete data[elem];
            });

            return this;
        },


        /**
         * Checks whether all of the specified elements has been registered.
         *
         * @param {Array} elems - An array of elements.
         * @returns {Boolean}
         */
        has: function(elems) {
            var data = this.data;

            return elems.every(function(elem) {
                return typeof data[elem] !== 'undefined';
            });
        }
    };

    return Storage;
});


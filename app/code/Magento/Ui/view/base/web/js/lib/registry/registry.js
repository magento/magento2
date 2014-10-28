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
    './storage',
    './events'
], function( storage, events ){
    'use strict';
    
    /**
     * Adds element to the storage and tries to resolve
            pending dependencies for it.   
     * @param {String} elem - Elements' name.
     * @param {*} value - Elements' value.
     */
    function set(elem, value) {
        storage.set(elem, value);
        events.resolve(elem);
    }


    /**
     * Tries to split the incoming string by spaces.
     * @params {(String|*)} st - String to split.
     * @returns {Array|*}
            The resulting array or the unmodified
            incoming value if first parameter wasn't a string.
     */
    function stringToArray(st) {
        return typeof st === 'string' ?
            st.split(' ') :
            st;
    }

    return {
        /**
         * Retrieves data from registry.
         * @params {(String|Array)} elems -
                An array of elements' names or a string of names divided by spaces.
         * @params {Function} [callback] -
                Callback function that will be triggered
                when all of the elements are registered.
         * @returns {Array|*|Undefined}
                Returns either an array of elements
                or an element itself if only is requested.
                If callback function is specified then returns 'undefined'.
         */
        get: function(elems, callback) {
            var records;

            elems = stringToArray(elems);

            if (typeof callback !== 'undefined') {
                events.wait(elems, callback);
            } else {
                records = storage.get(elems);

                return elems.length === 1 ?
                    records[0] :
                    records;
            }
        },


        /**
         * Sets data to registry.
         * @params {(String|Array|Object)} elems -
                An array of elements' names or a string of names divided by spaces.
                Also might be an object with element -> value pairs.
         * @params {*} [value] -
                Value that will be assigned to elements.
                This parameter is ignored when the first one
                represents an object of element -> value pairs.
         * @returns {registry} Chainable.  
         */
        set: function(elems, value) {
            var i;

            elems = stringToArray(elems);

            if (!Array.isArray(elems)) {

                for (i in elems) {
                    set(i, elems[i]);
                }
            } else {

                for (i = elems.length; i--;) {
                    set(elems[i], value)
                }
            }

            return this;
        },

        /**
         * Removes specified elements from a storage.
         * @params {(String|Array)} elems -
                An array of elements' names or a string of names divided by spaces.
         * @returns {registry} Chainable.
         */
        remove: function(elems) {
            storage.remove(stringToArray(elems));

            return this;
        },

        /**
         * Checks whether specified elements has been registered.
         * @params {(String|Array)} elems -
                An array of elements' names or a string of names divided by spaces.
         * @returns {Boolean}
         */
        has: function(elems) {
            return storage.has(stringToArray(elems));
        }

    };
});
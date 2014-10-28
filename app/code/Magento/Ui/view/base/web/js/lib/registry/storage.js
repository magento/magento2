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
define([], function(){
    'use strict';
    
    var data = {};

    return {
        /**
         * Retrieves values of the specified elements.
         * @param {Array} elems - An array of elements.
         * @returns {Array} Array of values. 
         */
        get: function(elems) {
            var result = [],
                record;

            elems.forEach(function(elem) {
                record = data[elem];

                result.push(record ? record.value : undefined);
            });

            return result;
        },


        /**
         * Sets key -> value pair.
         * @param {String} elem - Elements' name.
         * @param {*} value - Value of the element.
         * returns {storage} Chainable.
         */
        set: function(elem, value) {
            var record = data[elem] = data[elem] || {};

            record.value = value;

            return this;
        },


        /**
         * Removes specified elements from storage.
         * @param {Array} elems - An array of elements to be removed.
         * returns {storage} Chainable.
         */
        remove: function(elems) {
            elems.forEach(function(elem) {
                delete data[elem];
            });

            return this;
        },


        /**
         * Checks whether all of the specified elements has been registered.
         * @param {Array} elems - An array of elements.
         * @returns {Boolean}
         */
        has: function(elems) {
            return elems.every(function(elem) {
                return typeof data[elem] !== 'undefined';
            });
        }
    };
});

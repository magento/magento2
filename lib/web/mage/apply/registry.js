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

    var initialized = {};

    /**
     * Generates a unique identifier.
     * @returns {String}
     * @private
     */
    function uniqueid() {
        var idstr = String.fromCharCode((Math.random() * 25 + 65) | 0),
            ascicode;

        while (idstr.length < 5) {
            ascicode = Math.floor((Math.random() * 42) + 48);

            if (ascicode < 58 || ascicode > 64) {
                idstr += String.fromCharCode(ascicode);
            }
        }

        return idstr;
    }

    return {
        /**
         * Adds component to the initialized components list for the specified element.
         * @param {HTMLElement} el - Element whose component should be added.
         * @param {String} component - Components' name.
         */
        add: function(el, component) {
            var uid = el.uid,
                components;

            if (!uid) {
                el.uid = uid = uniqueid();
                initialized[uid] = [];
            }

            components = initialized[uid];

            if (!~components.indexOf(component)) {
                components.push(component);
            }
        },


        /**
         * Removes component from the elements' list.
         * @param {HTMLElement} el - Element whose component should be removed.
         * @param {String} component - Components' name.
         */
        remove: function(el, component) {
            var components;

            if (this.has(el, component)) {
                components = initialized[el.uid];

                components.splice(components.indexOf(component), 1);
            }
        },


        /**
         * Checks whether the specfied element has a component in its' list.
         * @param {HTMLElement} el - Element to check.
         * @param {String} component - Components' name.
         * @returns {Boolean}
         */
        has: function(el, component) {
            var components = initialized[el.uid];

            return components && ~components.indexOf(component);
        }
    };
});
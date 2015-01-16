/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils'
], function(utils){
    'use strict';

    var initialized = {};

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
                el.uid = uid = utils.uniqueid();
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
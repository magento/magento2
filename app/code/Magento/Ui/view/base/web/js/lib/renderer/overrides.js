/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function($) {
    'use strict';

    return {

        /**
         * Removes passed html element.
         * @param  {HTMLElement} oldPart - html element to remove
         */
        remove: function(oldPart) {
            $(oldPart).remove();
        },

        /**
         * Picks last node of newParts and replaces oldPart node with it.
         * @param  {HTMLElement} oldPart  - html element to replace
         * @param  {Array} newParts - array of html elements 
         */
        replace: function(oldPart, newParts) {
            var newPart = _.last(newParts);

            $(oldPart).replaceWith(newPart);
        },

        /**
         * Picks last node of newParts and replaces oldPart node with it's children.
         * @param  {HTMLElement} oldPart  - html element to replace
         * @param  {HTMLElement} newParts - array of html elements 
         */
        body: function(oldPart, newParts) {
            var newPart = _.last(newParts);

            $(oldPart).replaceWith(newPart.children);
        },

        /**
         * Picks the last item of newParts array and overides oldPart's html attributes with ones of it's own.
         * @param  {HTMLElement} oldPart - target html element to update
         * @param  {Array} newParts - array of html elements to get attributes from
         */
        update: function(oldPart, newParts) {
            var newPart = _.last(newParts);

            var attributes = newPart.attributes;
            var value, name;

            _.each(attributes, function(attr) {
                value = attr.value;
                name = attr.name;

                if (attr.name.indexOf('data-part') !== -1) {
                    return;
                }

                $(oldPart).attr(name, value);
            });
        },

        /**
         * Prepends oldPart with each html element's children from newParts array.
         * @param  {HTMLElement} oldPart - html element to prepend to
         * @param  {Array} newParts - array of html elements to get attributes from
         */
        prepend: function(oldPart, newParts) {
            newParts.forEach(function (node) {
                $(oldPart).prepend(node.children);
            });
        },

        /**
         * Appends oldPart with each html element's children from newParts array.
         * @param  {HTMLElement} oldPart - html element to append to
         * @param  {Array} newParts - array of html elements to get attributes from
         */
        append: function(oldPart, newParts) {
            newParts.forEach(function (node) {
                $(oldPart).append(node.children);
            });
        },

        /**
         * @return {Array} - array of strings representing available set of actions
         */
        getActions: function() {
            return 'replace remove body update append prepend'.split(' ');
        }
    };
});
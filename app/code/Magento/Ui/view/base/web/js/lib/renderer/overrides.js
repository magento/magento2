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
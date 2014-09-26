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
    './registry',
    'underscore',
    'require'
], function(registry, _, require) {
    'use strict';

    /**
     * Initializes components assigned to a specied element via data-* attribute.
     * @param {HTMLElement} el - Element to initialize components with.
     * @private
     */
    function initElement(el) {
        var data;

        data = el.getAttribute('data-mage-apply');
        data = JSON.parse(data);

        _.each(data, function(config, component){
            
            if (registry.has(el, component)) {
                return;
            }

            config = getConfig(el, component, config);

            require([component], function(callback) {
                registry.add(el, component);

                callback(el, config);
            });
        });
    }


    /**
     * Searches for a configurational object assigned to an element.
     * @param {HTMLElement} el - Element that will be used as a parentNode for a configurational element.
     * @param {String} component - Components name.
     * @param {String|Object} config - Configurational object or its' selector.
     * @returns {Object} Components' configuration.
     * @private
     */
    function getConfig(el, component, config) {
        var configNode,
            nodeData;

        configNode  = getConfigNode(config, component, el);
        nodeData    = configNode ? JSON.parse(configNode.firstChild.nodeValue) : {};

        return typeof config === 'object' ?
            _.extend(config, nodeData) :
            nodeData;
    }


    /**
     * Searches for a components' configurational node.
     * @param {string} selector - Configurational node selector.
     * @param {String} component - Components name.
     * @param {HTMLElement} parent - Element that will used as a parentNode for a configurational
            element in case if its' selector is not specified.
     * @returns {HTMLElement} Configurational node.
     * @private
     */
    function getConfigNode(selector, component, parent) {
        var node;

        if (parent.tagName === 'SCRIPT') {
            node = parent;
        }
        else{
            node = selector && typeof selector === 'string' ?
                document.querySelector(selector) :
                parent.querySelector('script[type="mage/config"]');

            if (!selector && (!node || node.parentNode !== parent)) {
                node = false;
            }
        }

        return node;
    }

    return {
        /**
         * Initializes components assigned to HTML elements via [data-mage-apply].
         */
        apply: function() {
            var elements;

            elements = document.querySelectorAll('[data-mage-apply]');

            elements = Array.prototype.slice.call(elements);

            elements.forEach(initElement);
        },

        /**
         * Creates a wrapper function on a jQuerys' component constructor.
         * @param {jQuery} $ - jQuery object.
         * @param {String} constr - Constructors' name.
         * returns {Function}
         */
        jqWrapper: function($, constr) {
            return function(el, data) {
                return $.fn[constr].call($(el), data);
            };
        }
    };
});
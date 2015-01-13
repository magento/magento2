/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    './registry',
    'require'
], function($, _, registry, require) {
    'use strict';

    var dataAttr        = 'data-mage-init',
        nodeSelector    = 'script[type="mage/config"]';

    /**
     * Initializes components assigned to a specied element via data-* attribute.
     * @param {HTMLElement} el - Element to initialize components with.
     * @param {Object|String} config - Initial components' config.
     * @param {String} component - Components' path.
     */
    function init(el, config, component) {
        
        if (registry.has(el, component)) {
            return;
        }

        registry.add(el, component);

        require([component], function(callback) {
            config = getConfig(el, config);

            if (typeof callback === 'object') {
                callback = callback[component];
            }

            if (_.isFunction(callback)) {
                callback(config, el);
            } else if ($(el)[component]) {
                $(el)[component](config);
            }
        });
    }


    /**
     * Searches for elements which has 'data-mage-init' attribute.
     * @param {HTMLElement} [parent=document.body] - Optional node inside of which to perform search.
     * @returns {Array} An array of elements with 'data-mage-init' attribute.
     */
    function getElements(parent){
        var elems;

        parent = parent || document.body;

        elems = parent.querySelectorAll('[' + dataAttr + ']');
        elems = Array.prototype.slice.call(elems);

        if(parent.hasAttribute(dataAttr)){
            elems.push(parent);
        }

        return elems;
    }


    /**
     * Parses elements 'data-mage-init' attribute as a valid JSON data.
     * @param {HTMLElement} el - Element whose attribute should be parsed.
     * @returns {Object}
     */
    function getData(el){
        var data = el.getAttribute(dataAttr);

        return JSON.parse(data);
    }


    /**
     * Searches for a components' configurational node.
     * @param {HTMLElement} el - Will be used as a parentNode for configurational element.
     * @param {Object|String} [config] - Initial config.
     * @returns {Object} Components' config.
     */
    function getConfig(el, config){
        var node,
            content;

        if(el.tagName === 'SCRIPT'){
            node = el;
        }
        else{
            node = el.querySelector(nodeSelector);

            if( node && node.parentNode !== el ){
                node = false;
            }
        }

        config  = config || {};
        content = node ? JSON.parse(node.textContent) : {};

        return _.extend(config, content);
    }

    return {
        /**
         * Initializes components assigned to HTML elements via [data-mage-init].
         * @param {HTMLElement} [ctx=document.body] - Optional node to search inside.
         */
        apply: function(ctx){
            var elems = getElements(ctx),
                data;

            elems.forEach(function(el){
                data = getData(el);

                _.each(data, init.bind(this, el));
            });
        },

        /**
         * Intializes single element with a specified component and config.
         * @param {HTMLElement} el - Element to initialize component on.   
         * @param {Object} config - Components' configuration.
         * @param {String} component - Components' path. 
         */
        applyFor: init
    };
});
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
/*jshint eqnull:true browser:true jquery:true expr:true */
/*global require:true console:true*/
(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            "mage/apply/main"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, mage) {
    "use strict";


    /**
     * Main namespace for Magento extensions
     * @type {Object}
     */
    $.mage = $.mage || {};

    /**
     * Plugin mage, initialize components on elements
     * @param {String} name - Components' path.
     * @param {Object} config - Components' config.
     * @returns {JQuery} Chainable.
     */
    $.fn.mage = function(name, config) {
        config = config || {};

        this.each(function(index, el){
            mage.applyFor(el, config, name);
        });

        return this;
    };
    
    $.extend($.mage, {
        /**
         * Handle all components declared via data attribute
         * @return {Object} $.mage
         */
        init: function() {
            mage.apply();

            return this;
        },

        /**
         * Method handling redirects and page refresh
         * @param {String} url - redirect URL
         * @param {(undefined|String)} type - 'assign', 'reload', 'replace'
         * @param {(undefined|Number)} timeout - timeout in milliseconds before processing the redirect or reload
         * @param {(undefined|Boolean)} forced - true|false used for 'reload' only
         */
        redirect: function(url, type, timeout, forced) {
            var _redirect;

            forced  = !!forced;
            timeout = timeout || 0;
            type    = type || "assign";
            
            _redirect = function() {
                window.location[type](type === 'reload' ? forced : url);
            };
            
            timeout ? setTimeout(_redirect, timeout) : _redirect();
        },


        /**
         * Checks if provided string is a valid selector.
         * @param {String} selector - Selector to check.
         * @returns {Boolean}
         */
        isValidSelector: function(selector){
            try {
                $(selector);

                return true;
            }
            catch(e){
                return false;
            }
        }
    });

    /**
     * Init components inside of dynamically updated elements
     */
    $('body').on('contentUpdated', function(e) {
        if(mage) {
            mage.apply(e.target);
        }
    });

    return $.mage;
}));
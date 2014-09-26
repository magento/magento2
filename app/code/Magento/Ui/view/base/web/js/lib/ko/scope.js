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
    'ko',
    '../class',
    './initialize'
], function(ko, Class) {
    'use strict';

    /**
     * Wrapper for ko.observable and ko.observableArray.
     * Assignes one or another ko property to obj[key]
     * @param  {Object} obj   - object to store property to
     * @param  {String} key   - key
     * @param  {*} value      - initial value of observable
     */
    function observe(obj, key, value){
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        obj[key] = ko[method](value);
    }

    return Class.extend({

        /**
         * If 2 params passed, path is considered as key.
         * Else, path is considered as object.
         * Assignes props to this based on incoming params
         * @param  {Object|String} path
         * @param  {*} value
         */
        observe: function(path, value) {
            var key;

            if (typeof path === 'string') {
                observe(this, path, value);
            } else {
                for (key in path) {
                    observe(this, key, path[key]);
                }
            }
        },

        /**
         * Reads it's params from provider and stores it into its params object
         * @return {Object} reference to instance
         */
        pushParams: function(){
            var params      = this.params,
                provider    = this.provider.params,
                data        = {};

            params.items.forEach(function(name) {
                data[name] = this[name]();
            }, this);

            provider.set(params.dir, data);

            return this;
        },

        /**
         * Loops over params.items and writes it's corresponding {key: value} 
         * pairs to this as observables.
         * @return {Object} reference to instance
         */
        pullParams: function(){
            var params      = this.params,
                provider    = this.provider.params,
                data        = provider.get(params.dir);

            params.items.forEach(function(name) {
                this[name](data[name]);
            }, this);

            return this;
        },

        /**
         * Calls pushParams and calls refresh on this.provider
         */
        reload: function() {
            this.pushParams()
                .provider.refresh();
        }
    });
});
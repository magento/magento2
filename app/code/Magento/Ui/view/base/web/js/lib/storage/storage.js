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
    'underscore',
    '../class',
    '../events'
], function(_, Class, EventsBus) {
    'use strict';

    return Class.extend({

        /**
         * Inits this.data to incoming data
         * @param  {Object} data
         */
        initialize: function(data) {
            this.data = data || {};
        },

        /**
         * If path specified, returnes this.data[path], else returns this.data
         * @param  {String} path
         * @return {*} this.data[path] or simply this.data
         */
        get: function(path) {
            return !path ? this.data : this.data[path];
        },

        /**
         * Sets value property to path and triggers update by path, passing result
         * @param {String|*} path
         * @param {Object} reference to instance
         */
        set: function(path, value){
            var result = this._override.apply(this, arguments);

            value   = result.value;
            path    = result.path;

            this.trigger('update', value);

            if (path) {
                this.trigger('update:' + path, value);
            }

            return this;
        },
        
        /**
         * Assignes props to this.data based on incoming params
         * @param  {String|*} path
         * @param  {*} value
         * @return {Object}
         */
        _override: function(path, value) {
            if (arguments.length > 1) {
                this.data[path] = value;
            } else {
                value = path;
                path = false;

                this.data = value;
            }

            return {
                path: path,
                value: value
            };
        }

    }, EventsBus);
});
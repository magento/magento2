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
    './abstract',
    'underscore'
], function (AbstractControl, _) {
    'use strict';
    
    return AbstractControl.extend({

        /**
         * Invokes initialize method of parent class and initializes observable properties of instance.
         * @param {Object} data - Item of "fields" array from grid configuration
         * @param {Object} config - Filter configuration
         */
        initialize: function (data, config) {
            this.constructor.__super__.initialize.apply(this, arguments);

            this.observe({
                from: '',
                to:   ''
            });
        },
        
        /**
         * Creates dump copy of current state.
         * @return {Object} dumped value object
         */
        getValues: function(){
            var value   = {},
                from    = this.from(),
                to      = this.to();

            if (from) {
                value.from = from;
            }

            if (to) {
                value.to = to;
            }

            return value;
        },

        /**
         * Returns string value of current state for UI
         * @return {String}
         */
        display: function(){
            var values = this.getValues();

            return _.map(values, function(value, name){
                return name + ': ' + value;
            }).join(' ');
        },

        /**
         * Checkes if current state is empty.
         * @return {Boolean}
         */
        isEmpty: function(){
            return ( !this.to() && !this.from() );
        },

        /**
         * Returns dump of instance's current state
         * @returns {Object} - object which represents current state of instance
         */
        dump: function () {
            this.output( this.display() );

            return {
                field: this.index,
                value: this.getValues()
            };
        },

        /**
         * Resets state properties of instance and calls dump method.
         * @returns {Object} - object which represents current state of instance
         */
        reset: function () {
            this.to('');
            this.from('');
        }
    });
});
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
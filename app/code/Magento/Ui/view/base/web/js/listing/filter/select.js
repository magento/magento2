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
         * Invokes initialize method of parent class and initializes properties of instance.
         * @param {Object} data - Item of "fields" array from grid configuration
         * @param {Object} config - Filter configuration
         */
        initialize: function (data) {
            this.constructor.__super__.initialize.apply(this, arguments);

            this.caption = 'Select...';

            this.observe('selected', '');

            this.options = this.options ? this.formatOptions(this.options) : [];
        },

        /**
         * Checkes if current state is empty.
         * @return {Boolean}
         */
        isEmpty: function(){
            var selected = this.selected();

            return !(selected && selected.value);
        },

        /**
         * Formats options property of instance.
         * @param {Object} options - object representing options
         * @returns {Array} - Options, converted to array
         */
        formatOptions: function (options) {
            return _.map(options, function (value, key) {
                return { value: key, label: value  };
            });
        },

        /**
         * Returns string value of current state for UI
         * @return {String}
         */
        display: function(){
            var selected = this.selected();

            return selected && selected.label;
        },

        /**
         * Returns dump of instance's current state
         * @returns {Object} - object which represents current state of instance
         */
        dump: function () {
            var selected = this.selected();

            this.output( this.display() );

            return {
                field: this.index,
                value: selected && selected.value
            }
        },

        /**
         * Resets state properties of instance and calls dump method.
         * @returns {Object} - object which represents current state of instance
         */
        reset: function () {
            this.selected(null);
        }
    });
});
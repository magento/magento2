/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/listing/filter/abstract',
    'underscore'
], function (AbstractControl, _) {
    'use strict';

    /**
     * Recursively loops through array of objects ({label: '...', value: '...'}
     *     or {label: '...', items: [...]}), looking for label, corresponding to value.
     * @param  {Array} arr
     * @param  {String} selected
     * @return {String} found label
     */
    function findIn(arr, selected) {
        var found;

        arr.some(function(obj){
            found = 'value' in obj ?
                obj.value == selected && obj.label :
                findIn(obj.items, selected);

            return found;
        });

        return found;
    }

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

            this.options = this.options || [];

            this.module = 'store';
        },

        /**
         * Checkes if current state is empty.
         * @return {Boolean}
         */
        isEmpty: function(){
            return !this.selected();
        },

        /**
         * Returns string value of current state for UI
         * @return {String}
         */
        display: function (selected) {
            var label = findIn(this.options, selected);
            
            return label;
        },

        /**
         * Returns dump of instance's current state
         * @returns {Object} - object which represents current state of instance
         */
        dump: function () {
            var selected = this.selected();

            this.output(this.display(selected));

            return {
                field: this.index,
                value: selected
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
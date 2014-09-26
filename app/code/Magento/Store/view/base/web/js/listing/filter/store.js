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
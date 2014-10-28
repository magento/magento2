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
    './storage'
], function(_, Storage) {
    'use strict';

    /**
     * Loops over first level of object looking for valueKey of typeof object values
     * to be typeof object as well. Breaks loop on first entry on one.
     * @param  {Object}  target
     * @param  {String}  valueKey - complex to look for
     * @return {Boolean}
     */
    function hasComplexValue(target, valueKey) {
        var result = false,
            key,
            object;


        for (key in target) {
            object = target[key];

            if (typeof object === 'object' && typeof object[valueKey] === 'object') {
                result = true;
                break;
            }
        }

        return result;
    }

    /**
     * Recursively loops over object's properties and converts it to array ignoring keys.
     * If typeof 'value' properties is 'object', creates 'items' property and assigns
     * execution of nestedObjectToArray on 'value' to it.
     * If typeof 'value' key is not an 'object', is simply writes an object itself to result array. 
     * @param  {Object} obj
     * @return {Array} result array
     */
    function nestedObjectToArray(obj, valueKey) {
        var target,
            items = [];

        for (var prop in obj) {

            target = obj[prop];
            if (typeof target[valueKey] === 'object') {

                target.items = nestedObjectToArray(target[valueKey], valueKey);
                delete target[valueKey];
            }
            items.push(target);
        }

        return items;
    }

    return Storage.extend({

        /**
         * Initializes data prop based on data argument.
         * Calls initFields and initColspan methods 
         * @param  {Object} config
         */
        initialize: function(data) {
            this.data = data || {};

            this.initFields()
                .initColspan();
        },

        /**
         * Formats fields property to compatible format.
         * Processes those. Assignes fiedls to data.fields.
         * @return {Object} - reference to instance
         */
        initFields: function(){
            var data    = this.data,
                fields  = data.fields;

            fields = this._fieldsToArray(fields);

            fields.forEach(this._processField, this);

            data.fields = fields;

            return this;
        },

        /**
         * Assigns data.colspan to this.getVisible().length
         * @return {Object} - reference to instance
         */
        initColspan: function(){
            var visible = this.getVisible();

            this.data.colspan = visible.length;

            return this;
        },

        /**
         * Assignes default params to field
         * @param  {Object} field
         * @return {Object} reference to instance
         */
        applyDefaults: function(field) {
            var defaults = this.data.defaults;

            if (defaults) {
                _.defaults(field, defaults);
            }

            return this;
        },

        /**
         * Format options based on those being nested
         * @param  {Object} field
         * @return {Object} reference to instance
         */
        formatOptions: function(field) {
            var result,
                options,
                isNested;

            options = field.options;

            if (options) {
                result      = {};
                isNested    = hasComplexValue(options, 'value');

                if(isNested){
                    result = nestedObjectToArray(options, 'value');
                }
                else{
                    _.each(options, function(option){
                        result[option.value] = option.label;
                    }); 
                }   
                                
                field.options = result;
            }

            return this;
        },

        /**
         * Returns filted by visible property fields array.
         * @return {Array} filted by visible property fields array
         */
        getVisible: function(){
            var fields  = this.data.fields;
            
            return fields.filter(function(field){
                return field.visible;
            });
        },

        /**
         * Convertes fields object to array, assigning key to index property.
         * @param  {Object} fields
         * @return {Array} array of fields
         */
        _fieldsToArray: function(fields){
            return _.map(fields, function(field, id){
                field.index = id;
                
                return field;
            });
        },

        /**
         * Calls applyDefaults and formatOptions on field
         * @param  {Object} field
         */
        _processField: function(field){
            this.applyDefaults(field)
                .formatOptions(field);
        }
    });
});
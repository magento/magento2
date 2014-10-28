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
    'Magento_Ui/js/lib/ko/scope',
    'Magento_Ui/js/lib/component'
], function(_, Scope, Component) {
    'use strict';

    var BASE_PATH = 'Magento_Ui/js/listing/filter';

    var defaults = {
        types: {
            filter_input:  BASE_PATH + '/input',
            filter_select: BASE_PATH + '/select',
            filter_range:  BASE_PATH + '/range',
            filter_date:   BASE_PATH + '/date'
        }
    };

    var Filter = Scope.extend({
        /**
         * Initializes instance properties 
         * @param {Object} config - Filter component configuration
         */
        initialize: function(config) {
            _.extend(this, config);

            this.initObservable()
                .loadControls();
        },

        /**
         * Callback method that proceeds initialization of filter component.
         */
        proceed: function () {
            this.extractFields()
                .initFilters();
        },

        /**
         * Initiates loading of filters constructors.
         */
        loadControls: function () {
            var coreTypes = defaults.types,
                paths,
                types;

            types = _.map(this.types, function (config, type) {
                config.name = type;

                if(!config.control){
                   config.control = coreTypes[type];
                }

                return config;
            });

            paths = _.pluck(types, 'control');

            require(paths, this.onControlsLoaded.bind(this, types));
        },

        /**
         * Initializes observable properties of instance.
         * @returns {Filter} Chainbale.
         */
        initObservable: function(){
            this.observe({
                isVisible:  false,
                active:     [],
                filters:    []
            });

            return this; 
        },

        /**
         * Filters filterable fields and stores them to this.fields 
         * @returns {Filter} Chainbale.
         */
        extractFields: function () {
            var provider    = this.provider.meta,
                fields      = provider.getVisible();

            this.fields = fields.filter(function (field) {
                return field.filterable;
            });

            return this;
        },

        /**
         * Initializes filters by creating instances of
         * corresponding classes found in controls by filter type.
         * @returns {Filter} Chainbale.
         */
        initFilters: function () {
            var controls = this.types,
                config,
                type,
                filters,
                control;

            filters = this.fields.map(function (field) {
                type    = (field.filter_type || field.input_type);
                config  = controls && controls[type];
                control = config.constr;

                field.type = type;

                return new control(field, config);
            }, this);

            this.filters(filters);

            return this;
        },

        /**
         * Extracts an array of non-empty filters.
         * @returns {Array} Array of non-empty filters
         */
        getNotEmpty: function(){
            return this.filters().filter(function(filter){
                return !filter.isEmpty();
            });
        },

        /**
         * Writes the result of getNotEmpty to active observable.
         * @returns {Filter} Chainbale.
         */
        findActive: function(){
            this.active(this.getNotEmpty());

            return this;
        },

        /**
         * Returns an array filters' data.
         * @param {Boolean} [all=false] -
                Whether to extract data from all of the filters
                or from only the active ones.
         * @returns {Array} Array of filters' data.
         */
        getData: function(all){
            var filters;

            filters = all ? this.filters() : this.active();

            return filters.map(function(filter){
                return filter.dump();
            });
        },

        /**
         * Clears data of all filters or of specified one.
         * @param {Object} [filter] - If specified, clears data only of this filter.
         * @returns {Filter} Chainbale.
         */
        clearData: function(filter){
            var active = this.active;

            if(filter){
                filter.reset();

                active.remove(filter);
            }
            else{
                this.filters().forEach(function (filter) {
                    filter.reset();
                });

                active.removeAll();
            }

            return this;
        },

        /**
         * Updates an array of active filters
         * and reloads data provider with new filtering parameters.
         * @returns {Filter} Chainbale.
         */
        apply: function () {
            this.findActive()
                .reload();

            return this;
        },

        /**
         * Clears filters and updates data provider with new filtering parameters.
         * @param {Object} [filter] - If specified then clears only this filter. 
         * @returns {Filter} Chainbale.
         */
        reset: function(filter){
            this.clearData(filter)           
                .reload();

            return this;
        },

        /**
         * Sets set of params to storage.
         * @param {*} action - data to set to storage params
         * @returns {Filter} Chainbale.
         */
        pushParams: function() {
            var params = this.provider.params;

            params.set('filter', this.getData());

            return this;
        },

        /**
         * @description Toggles isVisible observable property
         */
        toggle: function () {
            this.isVisible(!this.isVisible());
        },

        /**
         * @description Sets isVisible observable property to false
         */
        close: function () {
            this.isVisible(false);
        },

        /**
         * Returns path to filter's template splited by dots.
         * @param {Object} - instance of one of controls classes
         * @returns {String} - path to template based on type of filter
         */
        getTemplateFor: function (filter) {
            return 'ui/filter/' + filter.type;
        },

        /**
         * Resets specified filter using reset method
         * @param  {Object} filter - filter to reset
         */
        onClear: function(filter) {
            return this.reset.bind(this, filter);
        },

        /**
         * Callback that fires when all of the filters constructors has been loaded.
         * @param {Array} controlsMap - An array of availbale filter types and theirs configuration.       
         */
        onControlsLoaded: function (controlsMap) {
            var controls = Array.prototype.slice.call(arguments, 1),
                types = {},
                control;

            controls.forEach(function (constr, idx) {
                control = controlsMap[idx];

                delete control.control;
                
                control.constr = constr;

                types[control.name] = control;
            });

            this.types = types;

            this.proceed();
        }
    });

    return Component({
        constr: Filter
    });
});
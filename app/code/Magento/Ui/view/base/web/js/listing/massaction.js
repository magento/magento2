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
], function (_, Scope, Component) {
    'use strict';

    function capitaliseFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    var defaults = {
        actions: [],
        selects: [
            { value: 'selectAll',    label: 'Select all'                },
            { value: 'deselectAll',  label: 'Deselect all'              },
            { value: 'selectPage',   label: 'Select all on this page'   },
            { value: 'deselectPage', label: 'Deselect all on this page' }
        ],
        selectableTemplate: 'selectable'
    };

    var MassActions = Scope.extend({

        /**
         * Extends instance with defaults and config, initializes observable properties.
         * Updates storage with current state of instance.
         * @param {Object} config
         */
        initialize: function (config) {
            _.extend(this, defaults, config);

            this.initObservable()
                .initProperties()
                .formatActions()
                .attachTemplateExtender()
                .initListeners()
                .countPages();
        },

        /**
         * Initializes observable properties of instance.
         * @returns {MassActions} Chainable.
         */
        initObservable: function () {
            this.observe({
                selected:           this.selected || [],
                excluded:           [],
                allSelected:        this.allSelected || false,
                actionsVisible:     false,
                menuVisible:        false,
                hasMultiplePages:      ''
            });

            this.selected.subscribe(this.onSelectionsChange.bind(this));

            return this;
        },

        /**
         * Initializes instance properties
         * @returns {MassActions} Chainable.
         */
        initProperties: function () {
            var provider = this.provider.meta;

            this.indexField = provider.get('index_field');

            return this;
        },

        /**
         * Convertes incoming optins to compatible format
         * @returns {MassActions} Chainable.
         */
        formatActions: function(){
            var actions = this.actions;

            if(!Array.isArray(actions)){

                this.actions = _.map(actions, function(action, name){
                    action.value = name;

                    return action;
                });
            }

            return this;
        },

        /**
         * Attaches it's template to provider.dump's extenders
         * @returns {MassActions} Chainable.
         */
        attachTemplateExtender: function () {
            var provider    = this.provider,
                dump        = provider.dump,
                meta        = provider.meta,
                colspan     = meta.get('colspan'),
                extenders   = dump.get('extenders');

            if(!this.selectableTemplate) {
                return this;
            }

            extenders.push({
                path:   this.selectableTemplate,
                name:   this.name,
                as:     'massaction'
            });

            dump.trigger('update:extenders', extenders);
            meta.set('colspan', colspan + 1);

            return this;
        },

        /**
         * Init instance's subscribtions
         * @returns {MassActions} Chainable.
         */
        initListeners: function(){
            this.provider.on('refresh', this.onRefresh.bind(this));

            return this;
        },

        /**
         * Prepares params object, which represents the current state of instance.
         * @returns {Object} - params object
         */
        buildParams: function () {           
            if (this.allSelected()) {

                return {
                    all_selected: true,
                    excluded: this.excluded()
                };
            }

            return {
                selected: this.selected()
            };
        },
        
        /**
         * Toggles observable property based on area argument.
         * @param {String} area - Name of the area to be toggled.
         */
        toggle: function(area){
            var visible = this[area];

            visible(!visible());
        },

        /**
         * Sets actionsVisible to false
         */
        hideActions: function () {
            this.actionsVisible(false);
        },

        /**
         * Sets menuVisible to false
         */
        hideMenu: function () {
            this.menuVisible(false);
        },

        /**
         * Updates storage's params by the current state of instance
         * and hides dropdowns.
         * @param {String} action
         */
        setAction: function (action) {
            return function(){
                this.submit(action)
                    .hideActions();
            }.bind(this);
        },

        /**
         * Sends actions's data to server.
         * @param {Object} action - An action object.
         * @returns {MassActions} Chainable.
         */
        submit: function(action) {
            var client = this.provider.client;

            if (this.count) {

                client.submit({
                    method: 'post',
                    action: action.url,
                    data: {
                        massaction: this.buildParams()
                    }
                });
            } else {
                
                alert("You haven't selected any items!");
            }

            return this;
        },

        /**
         * Retrieve all id's from available records.
         * @param {Boolean} [exclude] - Whether to exclude not selected ids' from result.
         * @returns {Array} An array of ids'.
         */
        getIds: function(exclude){
            var items   = this.provider.data.get('items'),
                ids     = _.pluck(items, this.indexField);

            return exclude ?
                _.difference(ids, this.excluded()) :
                ids;    
        },

        /**
         * Sets isAllSelected observable to true and selects all items on current page.
         */
        selectAll: function () {
            this.allSelected(true);
            
            this.clearExcluded()
                .selectPage();
        },

        /**
         * Sets isAllSelected observable to false and deselects all items on current page.
         */
        deselectAll: function () {
            this.allSelected(false);
            this.deselectPage();
        },

        /**
         * Selects all items on current page, adding their ids to selected observable array
         */
        selectPage: function () {
            this.selected(this.getIds());
        },

        /**
         * Deselects all items on current page, emptying selected observable array
         */
        deselectPage: function () {
            this.selected.removeAll();
        },
        
        updateExcluded: function(selected) {
            var excluded    = this.excluded(),
                fromPage    = _.difference(this.getIds(), selected);

            excluded = _.union(excluded, fromPage);

            excluded = _.difference(excluded, selected);

            this.excluded(excluded);

            return this;
        },

        /**
         * Clears the array of not selected records.
         * @returns {MassActions} Chainable.
         */
        clearExcluded: function(){
            this.excluded.removeAll();

            return this;
        },

        /**
         * Returns handler for row click
         * @param  {String} url
         * @return {Function} click handler
         */
        redirectTo: function (url) {

            /**
             * Sets location.href to target url
             */
            return function () {
                window.location.href = url;
            }
        },

        /**
         * Gets current pages count and assignes it's being more than one to
         *     hasMultiplePages observable.
         * @returns {MassActions} Chainable.
         */
        countPages: function() {
            var provider = this.provider.data;

            this.pages = provider.get('pages');

            this.hasMultiplePages(this.pages > 1);

            return this;
        },

        /**
         * Counts number of 'selected' items. 
         * @returns {MassActions} Chainable.
         */
        countSelect: function() {
            var provider    = this.provider,
                total       = provider.data.get('totalCount'),
                excluded    = this.excluded().length,
                count       = this.selected().length;

            if (this.allSelected()) {
                count = total - excluded;
            }

            provider.meta.set('selected', count);

            this.count = count;

            return this;
        },

        /**
         * If isAllSelected is true, deselects all, else selects all
         */
        toggleSelectAll: function () {
            var isAllSelected = this.allSelected();

            isAllSelected ? this.deselectAll() : this.selectAll();
        },

        /**
         * Looks up for corresponding to passed action checker method,
         * and returnes it's result. If method not found, returnes true;
         * @param {String} action - e.g. selectAll, deselectAll
         * @returns {Boolean} should action be visible
         */
        shouldBeVisible: function (action) {
            var checker = this['should' + capitaliseFirstLetter(action) + 'BeVisible'];

            return checker ? checker.call(this) : true;
        },

        /**
         * Checkes if selectAll action supposed to be visible
         * @returns {Boolean}
         */
        shouldSelectAllBeVisible: function () {
            return !this.allSelected() && this.hasMultiplePages();
        },

        /**
         * Checkes if deselectAll action supposed to be visible
         * @returns {Boolean}
         */
        shouldDeselectAllBeVisible: function () {
            return this.allSelected() && this.hasMultiplePages();
        },

        onToggle: function(area){
            return this.toggle.bind(this, area);
        },

        /**
         * Creates handler for applying action (e.g. selectAll)
         * @param {String} action
         * @returns {Function} - click handler
         */
        onApplySelect: function (action) {
            return function(){
                this.menuVisible(false);
                this[action]();
            }.bind(this);
        },

        /**
         * Updates state according to changes of provider.
         */
        onRefresh: function () {
            if( this.allSelected() ){
                this.selected(this.getIds(true));
            }

            this.countPages();
        },

        onSelectionsChange: function(selected){
            this.updateExcluded(selected)
                .countSelect();
        }
    });

    return Component({
        constr: MassActions
    });
});
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiComponent'
], function (_, registry, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: true,
            sorting: false,
            visible: true,
            draggable: true,
            ignoreTmpls: {
                fieldAction: true
            },
            links: {
                visible: '${ $.storageConfig.path }.visible',
                sorting: '${ $.storageConfig.path }.sorting'
            },
            imports: {
                exportSorting: 'sorting'
            },
            listens: {
                '${ $.provider }:params.sorting.field': 'onSortChange'
            },
            modules: {
                source: '${ $.provider }'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Column} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('visible dragging dragover sorting');

            return this;
        },

        /**
         * Applies specified stored state of a column or one of its' properties.
         *
         * @param {String} state - Defines what state should be used: saved or default.
         * @param {String} [property] - Defines what columns' property should be applied.
         *      If not specfied, then all columns stored properties will be used.
         * @returns {Column} Chainable.
         */
        applyState: function (state, property) {
            var namespace = this.storageConfig.root;

            if (property) {
                namespace += '.' + property;
            }

            this.storage('applyState', state, namespace);

            return this;
        },

        /**
         * Sets columns' sorting. If column is currently sorted,
         * than its' direction will be toggled.
         *
         * @param {*} [enable=true] - If false, than sorting will
         *      be removed from a column.
         * @returns {Column} Chainable.
         */
        sort: function (enable) {
            var direction = false;

            if (!this.sortable) {
                return this;
            }

            if (enable !== false) {
                direction = this.toggleDirection();
            }

            this.sorting(direction);

            return this;
        },

        /**
         * Exports sorting data to the dataProvider if
         * sorting of a column is enabled.
         *
         * @param {(String|Boolean)} sorting - Columns' sorting state.
         */
        exportSorting: function (sorting) {
            if (!sorting) {
                return;
            }

            this.source('set', 'params.sorting', {
                field: this.index,
                direction: sorting
            });
        },

        /**
         * Toggles sorting direction.
         *
         * @returns {String} New direction.
         */
        toggleDirection: function () {
            return this.sorting() === 'asc' ?
                'desc' :
                'asc';
        },

        /**
         * Checks if column has an assigned action that will
         * be performed when clicking on one of its' fields.
         *
         * @returns {Boolean}
         */
        hasFieldAction: function () {
            return !!this.fieldAction;
        },

        /**
         * Applies action described in a 'fieldAction' property.
         *
         * @param {Number} rowIndex - Index of a row which initiates action.
         * @returns {Column} Chainable.
         *
         * @example Example of fieldAction definition, which is equivalent to
         *      referencing to external component named 'listing.multiselect'
         *      and calling its' method 'toggleSelect' with params [rowIndex, true] =>
         *
         *      {
         *          provider: 'listing.multiselect',
         *          target: 'toggleSelect',
         *          params: ['${ $.$data.rowIndex }', true]
         *      }
         */
        applyFieldAction: function (rowIndex) {
            var action = this.fieldAction,
                callback;

            if (!this.hasFieldAction()) {
                return this;
            }

            action = utils.template(action, {
                column: this,
                rowIndex: rowIndex
            }, true);

            callback = this._getFieldCallback(action);

            if (_.isFunction(callback)) {
                callback();
            }

            return this;
        },

        /**
         * Creates action callback based on its' data.
         *
         * @param {Object} action - Actions' object.
         * @returns {Function|Boolean} Callback function or false
         *      value if it was imposible create a callback.
         */
        _getFieldCallback: function (action) {
            var args     = action.params || [],
                callback = action.target;

            if (action.provider && action.target) {
                args.unshift(action.target);

                callback = registry.async(action.provider);
            }

            if (!_.isFunction(callback)) {
                return false;
            }

            return function () {
                callback.apply(callback, args);
            };
        },

        /**
         * Ment to preprocess data associated with a current columns' field.
         *
         * @param {*} data - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (data) {
            return data;
        },

        /**
         * Returns path to the columns' header template.
         *
         * @returns {String}
         */
        getHeader: function () {
            return this.headerTmpl;
        },

        /**
         * Returns path to the columns' body template.
         *
         * @returns {String}
         */
        getBody: function () {
            return this.bodyTmpl;
        },

        /**
         * Listener of the providers' sorting state changes.
         *
         * @param {Srting} field - Field by which current sorting is performed.
         */
        onSortChange: function (field) {
            if (field !== this.index) {
                this.sort(false);
            }
        }
    });
});

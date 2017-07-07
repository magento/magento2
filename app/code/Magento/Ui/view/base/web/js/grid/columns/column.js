/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiElement'
], function (_, registry, utils, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            disableAction: false,
            controlVisibility: true,
            sortable: true,
            sorting: false,
            visible: true,
            draggable: true,
            fieldClass: {},
            ignoreTmpls: {
                fieldAction: true
            },
            statefull: {
                visible: true,
                sorting: true
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
         * Initializes column component.
         *
         * @returns {Column} Chainable.
         */
        initialize: function () {
            this._super()
                .initFieldClass();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Column} Chainable.
         */
        initObservable: function () {
            this._super()
                .track([
                    'visible',
                    'sorting',
                    'disableAction'
                ])
                .observe([
                    'dragging'
                ]);

            return this;
        },

        /**
         * Extends list of field classes.
         *
         * @returns {Column} Chainable.
         */
        initFieldClass: function () {
            _.extend(this.fieldClass, {
                _dragging: this.dragging
            });

            return this;
        },

        /**
         * Applies specified stored state of a column or one of its' properties.
         *
         * @param {String} state - Defines what state should be used: saved or default.
         * @param {String} [property] - Defines what columns' property should be applied.
         *      If not specified, then all columns stored properties will be used.
         * @returns {Column} Chainable.
         */
        applyState: function (state, property) {
            var namespace = this.storageConfig.root;

            if (property) {
                namespace += '.' + property;
            }

            this.storage('applyStateOf', state, namespace);

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
            if (!this.sortable) {
                return this;
            }

            enable !== false ?
                this.toggleSorting() :
                this.sorting = false;

            return this;
        },

        /**
         * Sets descending columns' sorting.
         *
         * @returns {Column} Chainable.
         */
        sortDescending: function () {
            if (this.sortable) {
                this.sorting = 'desc';
            }

            return this;
        },

        /**
         * Sets ascending columns' sorting.
         *
         * @returns {Column} Chainable.
         */
        sortAscending: function () {
            if (this.sortable) {
                this.sorting = 'asc';
            }

            return this;
        },

        /**
         * Toggles sorting direction.
         *
         * @returns {Column} Chainable.
         */
        toggleSorting: function () {
            this.sorting === 'asc' ?
                this.sortDescending() :
                this.sortAscending();

            return this;
        },

        /**
         * Checks if column is sorted.
         *
         * @returns {Boolean}
         */
        isSorted: function () {
            return !!this.sorting;
        },

        /**
         * Exports sorting data to the dataProvider if
         * sorting of a column is enabled.
         */
        exportSorting: function () {
            if (!this.sorting) {
                return;
            }

            this.source('set', 'params.sorting', {
                field: this.index,
                direction: this.sorting
            });
        },

        /**
         * Checks if column has an assigned action that will
         * be performed when clicking on one of its' fields.
         *
         * @returns {Boolean}
         */
        hasFieldAction: function () {
            return !!this.fieldAction || !!this.fieldActions;
        },

        /**
         * Applies action described in a 'fieldAction' property
         * or actions described in 'fieldActions' property.
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
            if (!this.hasFieldAction() || this.disableAction) {
                return this;
            }

            if (this.fieldActions) {
                this.fieldActions.forEach(this.applySingleAction.bind(this, rowIndex), this);
            } else {
                this.applySingleAction(rowIndex);
            }

            return this;
        },

        /**
         * Applies single action
         *
         * @param {Number} rowIndex - Index of a row which initiates action.
         * @param {Object} action - Action (fieldAction) to be applied
         *
         */
        applySingleAction: function (rowIndex, action) {
            var callback;

            action = action || this.fieldAction;
            action = utils.template(action, {
                column: this,
                rowIndex: rowIndex
            }, true);

            callback = this._getFieldCallback(action);

            if (_.isFunction(callback)) {
                callback();
            }
        },

        /**
         * Returns field action handler if it was specified.
         *
         * @param {Object} record - Record object with which action is associated.
         * @returns {Function|Undefined}
         */
        getFieldHandler: function (record) {
            if (this.hasFieldAction()) {
                return this.applyFieldAction.bind(this, record._rowIndex);
            }
        },

        /**
         * Creates action callback based on its' data.
         *
         * @param {Object} action - Actions' object.
         * @returns {Function|Boolean} Callback function or false
         *      value if it was impossible create a callback.
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
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (record) {
            return record[this.index];
        },

        /**
         * Returns list of classes that should be applied to a field.
         *
         * @returns {Object}
         */
        getFieldClass: function () {
            return this.fieldClass;
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

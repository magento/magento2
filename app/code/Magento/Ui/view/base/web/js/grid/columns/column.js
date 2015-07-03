/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiComponent'
], function (_, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: true,
            sorting: false,
            visible: true,
            draggable: true,
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
         *      If not specfied, than all collumns stored properties will be used.
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
            var direction;

            if (!this.sortable) {
                return this;
            }

            enable = enable !== false ? true : false;

            direction = enable ?
                this.sorting() ?
                    this.toggleDirection() :
                    'asc' :
                false;

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
         * Toggles sorting direcction.
         *
         * @returns {String} New direction.
         */
        toggleDirection: function () {
            return this.sorting() === 'asc' ?
                'desc' :
                'asc';
        },

        getClickUrl: function (row) {
            var field = row[this.actionField],
                action = field && field[this.clickAction];

            return action ? action.href : '';
        },

        isClickable: function (row) {
            return !!this.getClickUrl(row);
        },

        redirect: function (url) {
            window.location.href = url;
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

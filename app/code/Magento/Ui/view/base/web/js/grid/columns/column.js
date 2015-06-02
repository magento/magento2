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
            dragging: false,
            dragover: false,
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

        applyState: function (property, state) {
            var storage = this.storage(),
                namespace = this.storageConfig.root + '.' + property,
                data,
                value;

            if (state === 'default') {
                data = storage.getDefault();
            } else if (state === 'last') {
                data = storage.getSaved();
            }

            value = utils.nested(data, namespace);

            if (!_.isUndefined(value)) {
                this.set(property, value);
            }
        },

        sort: function (enabled) {
            var direction;

            if (!this.sortable) {
                return;
            }

            direction = enabled !== false ?
                this.sorting() ?
                    this.toggleDirection() :
                    'asc' :
                false;

            this.sorting(direction);
        },

        exportSorting: function (sorting) {
            if (!sorting) {
                return;
            }

            this.source('set', 'params.sorting', {
                field: this.index,
                direction: sorting
            });
        },

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

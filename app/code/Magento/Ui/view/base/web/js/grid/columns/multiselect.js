/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/multiselect',
            bodyTmpl: 'ui/grid/cells/multiselect',
            menuVisible: false,
            allSelected: false,
            selected: [],
            excluded: [],
            actions: [{
                value: 'selectAll',
                label: 'Select all'
            }, {
                value: 'deselectAll',
                label: 'Deselect all'
            }, {
                value: 'selectPage',
                label: 'Select all on this page'
            }, {
                value: 'deselectPage',
                label: 'Deselect all on this page'
            }],

            exports: {
                totalSelected: '<%= provider %>:data.totalSelected'
            },

            imports: {
                totalRecords: '<%= provider %>:data.totalRecords',
                rows: '<%= provider %>:data.items'
            },

            listens: {
                selected: 'onSelectedChange'
            }
        },

        initObservable: function () {
            this._super()
                .observe('menuVisible selected excluded allSelected');

            return this;
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
         * If isAllSelected is true, deselects all, else selects all
         */
        toggleSelectAll: function () {
            var isAllSelected = this.allSelected();

            isAllSelected ? this.deselectAll() : this.selectAll();
        },

        /**
         * Selects all items on current page, adding their ids to selected observable array.
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

        /**
         * Clears the array of not selected records.
         * @returns {MassActions} Chainable.
         */
        clearExcluded: function () {
            this.excluded.removeAll();

            return this;
        },

        /**
         * Retrieve all id's from available records.
         * @param {Boolean} [exclude] - Whether to exclude not selected ids' from result.
         * @returns {Array} An array of ids'.
         */
        getIds: function (exclude) {
            var items = this.rows(),
                ids = _.pluck(items, this.indexField);

            return exclude ?
                _.difference(ids, this.excluded()) :
                ids;
        },

        updateExcluded: function (selected) {
            var excluded = this.excluded(),
                fromPage = _.difference(this.getIds(), selected);

            excluded = _.union(excluded, fromPage);
            excluded = _.difference(excluded, selected);

            this.excluded(excluded);

            return this;
        },

        countSelected: function () {
            var total = this.totalRecords(),
                excluded = this.excluded().length,
                count = this.selected().length;

            if (this.allSelected()) {
                count = total - excluded;
            }

            this.totalSelected(count);
        },

        toggleMenu: function () {
            this.menuVisible(!this.menuVisible());
        },

        hideMenu: function () {
            this.menuVisible(false);
        },

        isSelectVisible: function (action) {
            var onPage = this.getIds().length,
                selected = this.selected(),
                total = this.totalRecords();

            switch (action) {
                case 'selectPage':
                case 'deselectPage':
                    return onPage < total;

                case 'deselectAll':
                case 'deselectPage':
                    return !!selected.length;

                default:
                    return true;
            }
        },

        onSelectedChange: function (selected) {
            this.updateExcluded(selected)
                .countSelected();
        }
    });
});

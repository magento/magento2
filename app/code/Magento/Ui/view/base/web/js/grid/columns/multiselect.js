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
            excludeMode: false,
            allSelected: false,
            indetermine: false,
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

            imports: {
                totalRecords: '<%= provider %>:data.totalRecords',
                rows: '<%= provider %>:data.items'
            },

            listens: {
                selected: 'onSelectedChange',
                rows: 'onRowsChange'
            }
        },

        initObservable: function () {
            this._super()
                .observe('menuVisible selected excluded excludeMode totalSelected allSelected indetermine');

            return this;
        },

        /**
         * Toggles menu with a list of select actions.
         */
        toggleMenu: function () {
            this.menuVisible(!this.menuVisible());
        },

        /**
         * Hides menu with a list of select actions.
         */
        hideMenu: function () {
            this.menuVisible(false);
        },

        /**
         * Selects all grid records, even those that are not visible on the page.
         */
        selectAll: function () {
            this.excludeMode(true);

            this.clearExcluded()
                .selectPage();
        },

        /**
         * Sets isAllSelected observable to false and deselects all items on current page.
         */
        deselectAll: function () {
            this.excludeMode(false);

            this.clearExcluded()
                .deselectPage();
        },

        /**
         * If isAllSelected is true, deselects all, else selects all
         */
        toggleSelectAll: function () {
            return this.allSelected() ?
                this.deselectAll() :
                this.selectAll();
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
                selected = this.selected().length;

            if (this.excludeMode()) {
                selected = total - excluded;
            }

            this.totalSelected(selected);

            return this;
        },

        exportSelections: function () {
            var data;

            if (this.excludeMode()) {
                data = {
                    all_selected: true,
                    excluded: this.excluded()
                };
            } else {
                data = {
                    selected: this.selected()
                };
            }

            data.totalSelected = this.totalSelected();

            this.source.set('config.multiselect', data);
        },

        /**
         * Defines if provided select action should be visible.
         *
         * @param {String} actionId - Id of the action to be checked.
         * @returns {Boolean}
         */
        isSelectVisible: function (actionId) {
            var pageIds = this.getIds().length,
                multiplePages = pageIds < this.totalRecords();

            switch (actionId) {
                case 'selectPage':
                    return multiplePages && !this.isPageSelected(true);

                case 'deselectPage':
                    return multiplePages && this.isPageSelected();

                case 'selectAll':
                    return !this.allSelected();

                case 'deselectAll':
                    return this.totalSelected() > 0;
            }

            return true;
        },

        /**
         * Defines if current page has selected items.
         *
         * @param {Boolean} [all=false] - If set as 'true' checks that every
         *      item on the page is selected. Otherwise checks that
         *      page has some selected items.
         * @returns {Boolean}
         */
        isPageSelected: function (all) {
            var pageIds = this.getIds(),
                selected = this.selected(),
                excluded = this.excluded(),
                iterator = all ? 'every' : 'some';

            if (this.allSelected()) {
                return true;
            }

            if (this.excludeMode()) {
                return pageIds[iterator](function (id) {
                    return !~excluded.indexOf(id);
                });
            }

            return pageIds[iterator](function (id) {
                return !!~selected.indexOf(id);
            });
        },

        /**
         * Updates values of the 'allSelected'
         * and 'indetermine' properties.
         */
        updateState: function () {
            var selected        = this.selected().length,
                excluded        = this.excluded().length,
                totalSelected   = this.totalSelected(),
                totalRecords    = this.totalRecords(),
                allSelected     = totalSelected === totalRecords;

            if (this.excludeMode()) {
                if (excluded === totalRecords) {
                    this.deselectAll();
                }
            } else if (selected === totalRecords) {
                this.selectAll();
            }

            this.allSelected(allSelected);
            this.indetermine(totalSelected > 0 && !allSelected);

            return this;
        },

        /**
         * Callback method to handle change of the selected items.
         *
         * @param {Array} selected - List of the currently selected items.
         */
        onSelectedChange: function (selected) {
            this.updateExcluded(selected)
                .countSelected()
                .updateState()
                .exportSelections();
        },

        /**
         * Is invoked when rows has changed. Recalculates selected items
         * based on "selectMode" property.
         */
        onRowsChange: function () {
            var newSelected;

            if (this.excludeMode()) {
                newSelected = _.union(this.getIds(true), this.selected());

                this.selected(newSelected);
            }
        }
    });
});

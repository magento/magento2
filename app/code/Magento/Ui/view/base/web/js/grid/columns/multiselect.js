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
            selectMode: 'selected',
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

            imports: {
                totalRecords: '<%= provider %>:data.totalRecords',
                rows: '<%= provider %>:data.items'
            },

            listens: {
                selected: 'onSelectedChange',
                '<%= provider %>:data.items': 'onRowsChange'
            }
        },

        initObservable: function () {
            this._super()
                .observe('menuVisible selected excluded selectMode totalSelected allSelected');

            return this;
        },

        /**
         * Sets isAllSelected observable to true and selects all items on current page.
         */
        selectAll: function () {
            this.selectMode('all');
            this.allSelected(true);

            this.clearExcluded()
                .selectPage();
        },

        /**
         * Sets isAllSelected observable to false and deselects all items on current page.
         */
        deselectAll: function () {
            this.selectMode('selected');
            this.allSelected(false);
            this.deselectPage();
        },

        /**
         * If isAllSelected is true, deselects all, else selects all
         */
        toggle: function () {
            var selectMode = this.selectMode(),
                hasItems = this.totalRecords();

            if (hasItems) {
                selectMode === 'selected' ? this.selectAll() : this.deselectAll();
            }
        },

        /**
         * Selects all items on current page, adding their ids to selected observable array.
         * @returns {MassActions} Chainable.
         */
        selectPage: function () {
            this.selected(this.getIds());

            return this;
        },

        /**
         * Deselects all items on current page, emptying selected observable array
         * @returns {MassActions} Chainable.
         */
        deselectPage: function () {
            this.selected.removeAll();

            return this;
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
            var totalCount = this.totalRecords(),
                excludedCount = this.excluded().length,
                count = this.selected().length,
                selectMode = this.selectMode(),
                hasNoExcluded = !excludedCount;

            if (selectMode === 'all') {
                count = totalCount - excludedCount;
            }

            this.allSelected(hasNoExcluded);

            this.totalSelected(count);

            return this;
        },

        /**
         * Toggles menu visible state
         */
        toggleMenu: function () {
            this.menuVisible(!this.menuVisible());
        },

        /**
         * Hides menu
         */
        hideMenu: function () {
            this.menuVisible(false);
        },

        /**
         * Exports component data to source by 'config.multiselect' namespace
         */
        exportSelections: function () {
            var data,
                selectMode = this.selectMode();

            if (selectMode === 'all') {
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
         * Defines whether the action should be visible or not.
         */
        isSelectVisible: function (action) {
            var ids                 = this.getIds(),
                idsCount            = ids.length,
                totalCount          = this.totalRecords(),
                selected            = this.selected(),
                selectMode          = this.selectMode(),
                hasSelections       = selected.length,
                pageIsNotSelected   = _.difference(ids, selected).length,
                pageHasSelections   = _.intersection(ids, selected).length;

            if (!ids.length) {
                return false;
            }

            switch (action) {
                case 'selectPage':
                    return idsCount < totalCount && pageIsNotSelected;

                case 'selectAll':
                    return selectMode === 'selected';

                case 'deselectAll':
                    return hasSelections;

                case 'deselectPage':
                    return pageHasSelections;

                default:
                    return true;
            }
        },

        /**
         * Is invoked when "selected" property has changed.
         *
         * @param   {Array} selected - The list of selected ids
         */
        onSelectedChange: function (selected) {
            this.updateExcluded(selected)
                .updateSelectMode()
                .countSelected()
                .exportSelections();
        },

        /**
         * Sets "selectMode" to "selected", if all items have been unchecked
         *     after "selectAll" action is performed.
         *
         * @returns {MassActions} Chainable.
         */
        updateSelectMode: function () {
            var excludedCount   = this.excluded().length,
                noSelected      = !this.selected().length,
                totalCount      = this.totalRecords(),
                allExcluded     = excludedCount === totalCount,
                selectAllMode   = this.selectMode() === 'all';

            if (noSelected && allExcluded && selectAllMode) {
                this.selectMode('selected');
            }

            return this;
        },

        /**
         * Is invoked when "provider.items" has changed. Recalculates selected items
         *     based on "selectMode" property.
         */
        onRowsChange: function () {
            var selectMode          = this.selectMode(),
                newIds              = this.getIds(true),
                previouslySelected  = this.selected(),
                newSelected;

            if (selectMode === 'all') {
                newSelected = _.union(newIds, previouslySelected);

                this.selected(newSelected);
            }
        },

        /**
         * Defines if the state of main checkbox shoud be 'indeterminated'.
         *
         * @returns {Boolean}
         */
        isIndeterminate: function () {
            var ids = this.getIds(),
                hasFewItems = ids.length > 1,
                selectMode = this.selectMode(),
                hasSelected = this.selected().length,
                hasExcluded = this.excluded().length;

            return hasFewItems && (selectMode === 'all' ? hasExcluded : hasSelected);
        }
    });
});

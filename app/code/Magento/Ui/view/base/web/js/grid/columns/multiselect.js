/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/translate',
    './column'
], function (_, $t, Column) {
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
                label: $t('Select all')
            }, {
                value: 'deselectAll',
                label: $t('Deselect all')
            }, {
                value: 'selectPage',
                label: $t('Select all on this page')
            }, {
                value: 'deselectPage',
                label: $t('Deselect all on this page')
            }],

            imports: {
                totalRecords: '${ $.provider }:data.totalRecords',
                rows: '${ $.provider }:data.items'
            },

            listens: {
                '${ $.provider }:params.filters': 'deselectAll',
                selected: 'onSelectedChange',
                rows: 'onRowsChange'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Multiselect} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'menuVisible',
                    'selected',
                    'excluded',
                    'excludeMode',
                    'totalSelected',
                    'allSelected',
                    'indetermine',
                    'totalRecords',
                    'rows'
                ]);

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

            return this;
        },

        /**
         * Deselects all grid records.
         */
        deselectAll: function () {
            this.excludeMode(false);

            this.clearExcluded()
                .deselectPage();
            this.selected.removeAll();

            return this;
        },

        /**
         * Selects or deselects all records.
         */
        toggleSelectAll: function () {
            return this.allSelected() ?
                    this.deselectAll() :
                    this.selectAll();
        },

        /**
         * Selects all records on the current page.
         */
        selectPage: function () {
            this.selected(
                _.union(this.selected(), this.getIds())
            );

            return this;
        },

        /**
         * Deselects all records on the current page.
         */
        deselectPage: function () {
            var currentPageIds = this.getIds();
            this.selected.remove(function (value) {
                return currentPageIds.indexOf(value) !== -1;
            });

            return this;
        },

        /**
         * Clears the array of not selected records.
         *
         * @returns {Multiselect} Chainable.
         */
        clearExcluded: function () {
            this.excluded.removeAll();

            return this;
        },

        /**
         * Retrieve all id's from available records.
         *
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

        /**
         * Recalculates list of the excluded records.
         * Changes value of `excluded`.
         *
         * @param {Array} selected - List of the currently selected records.
         * @returns {Multiselect} Chainable.
         */
        updateExcluded: function (selected) {
            var excluded = this.excluded(),
                fromPage = _.difference(this.getIds(), selected);

            excluded = _.union(excluded, fromPage);
            excluded = _.difference(excluded, selected);

            this.excluded(excluded);

            return this;
        },

        /**
         * Calculates number of the selected records.
         * Changes value of `totalSelected`.
         *
         * @returns {Multiselect} Chainable.
         */
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

        /**
         * Returns selections data.
         *
         * @returns {Object}
         */
        getSelections: function () {
            return {
                excluded: this.excluded(),
                selected: this.selected(),
                total: this.totalSelected(),
                excludeMode: this.excludeMode()
            };
        },

        /**
         * Defines if provided select/deselect action is relevant.
         *
         * @param {String} actionId - Id of the action to be checked.
         * @returns {Boolean}
         */
        isActionRelevant: function (actionId) {
            var pageIds = this.getIds().length,
                multiplePages = pageIds < this.totalRecords(),
                result = true;

            switch (actionId) {
                case 'selectPage':
                    result = multiplePages && !this.isPageSelected(true);
                    break;

                case 'deselectPage':
                    result =  multiplePages && this.isPageSelected();
                    break;

                case 'selectAll':
                    result = !this.allSelected();
                    break;

                case 'deselectAll':
                    result = this.totalSelected() > 0;
            }

            return result;
        },

        /**
         * Defines if current page has selected records on it.
         *
         * @param {Boolean} [all=false] - If set to 'true' checks that every
         *      record on the page is selected. Otherwise checks that
         *      page has some selected records.
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
                allSelected     = totalRecords && totalSelected === totalRecords;

            if (this.excludeMode()) {
                if (excluded === totalRecords) {
                    this.deselectAll();
                }
            } else if (totalRecords && selected === totalRecords) {
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
                .updateState();
        },

        /**
         * Is invoked when rows has changed. Recalculates selected items
         * based on "selectMode" property.
         */
        onRowsChange: function () {
            var newSelections;

            if (this.excludeMode()) {
                newSelections = _.union(this.getIds(true), this.selected());

                this.selected(newSelections);
            }
        }
    });
});

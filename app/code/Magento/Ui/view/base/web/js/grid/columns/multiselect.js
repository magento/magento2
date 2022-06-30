/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
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
            controlVisibility: false,
            sortable: false,
            draggable: false,
            menuVisible: false,
            excludeMode: false,
            allSelected: false,
            indetermine: false,
            preserveSelectionsOnFilter: false,
            disabled: [],
            selected: [],
            excluded: [],
            fieldClass: {
                'data-grid-checkbox-cell': true
            },
            actions: [{
                value: 'selectAll',
                label: $t('Select All')
            }, {
                value: 'deselectAll',
                label: $t('Deselect All')
            }, {
                value: 'selectPage',
                label: $t('Select All on This Page')
            }, {
                value: 'deselectPage',
                label: $t('Deselect All on This Page')
            }],

            imports: {
                totalRecords: '${ $.provider }:data.totalRecords',
                rows: '${ $.provider }:data.items'
            },

            listens: {
                '${ $.provider }:params.filters': 'onFilter',
                '${ $.provider }:params.search': 'onSearch',
                selected: 'onSelectedChange',
                rows: 'onRowsChange'
            },

            modules: {
                source: '${ $.provider }'
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
                    'disabled',
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
         * Selects specified record.
         *
         * @param {*} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Multiselect} Chainable.
         */
        select: function (id, isIndex) {
            this._setSelection(id, isIndex, true);

            return this;
        },

        /**
         * Deselects specified record.
         *
         * @param {*} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Multiselect} Chainable.
         */
        deselect: function (id, isIndex) {
            this._setSelection(id, isIndex, false);

            return this;
        },

        /**
         * Toggles selection of a specified record.
         *
         * @param {*} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Multiselect} Chainable.
         */
        toggleSelect: function (id, isIndex) {
            this._setSelection(id, isIndex, !this.isSelected(id, isIndex));

            return this;
        },

        /**
         * Checks if specified record is selected.
         *
         * @param {*} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @returns {Boolean}
         */
        isSelected: function (id, isIndex) {
            id = this.getId(id, isIndex);

            return this.selected.contains(id);
        },

        /**
         * Selects/deselects specified record base on a 'select' parameter value.
         *
         * @param {*} id - See definition of 'getId' method.
         * @param {Boolean} [isIndex=false] - See definition of 'getId' method.
         * @param {Boolean} select - Whether to select/deselect record.
         * @returns {Multiselect} Chainable.
         */
        _setSelection: function (id, isIndex, select) {
            var selected = this.selected;

            id = this.getId(id, isIndex);

            if (!select && this.isSelected(id)) {
                selected.remove(id);
            } else if (select) {
                selected.push(id);
            }

            return this;
        },

        /**
         * Selects all records, even those that
         * are not visible on the page.
         *
         * @returns {Multiselect} Chainable.
         */
        selectAll: function () {
            this.excludeMode(true);

            this.clearExcluded()
                .selectPage();

            return this;
        },

        /**
         * Deselects all records.
         *
         * @returns {Multiselect} Chainable.
         */
        deselectAll: function () {
            this.excludeMode(false);

            this.clearExcluded();
            this.selected.removeAll();

            return this;
        },

        /**
         * Selects or deselects all records.
         *
         * @returns {Multiselect} Chainable.
         */
        toggleSelectAll: function () {
            this.allSelected() ?
                this.deselectAll() :
                this.selectAll();

            return this;
        },

        /**
         * Selects all records on the current page.
         *
         * @returns {Multiselect} Chainable.
         */
        selectPage: function () {
            var selected = _.union(this.selected(), this.getIds());

            selected = _.difference(selected, this.disabled());

            this.selected(selected);

            return this;
        },

        /**
         * Deselects all records on the current page.
         *
         * @returns {Multiselect} Chainable.
         */
        deselectPage: function () {
            var pageIds = this.getIds();

            this.selected.remove(function (value) {
                return !!~pageIds.indexOf(value);
            });

            return this;
        },

        /**
        * Selects or deselects all records on the current page.
        *
        * @returns {Multiselect} Chainable.
        */
        togglePage: function () {
            return this.isPageSelected() && !this.excluded().length ? this.deselectPage() : this.selectPage();
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
         * Returns identifier of a record.
         *
         * @param {*} id - Id of a record or its' index in a rows array.
         * @param {Boolean} [isIndex=false] - Flag that specifies with what
         *      kind of identifier we are dealing with.
         * @returns {*}
         */
        getId: function (id, isIndex) {
            var record = this.rows()[id];

            if (isIndex && record) {
                id = record[this.indexField];
            }

            return id;
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
         * Calculates number of selected records and
         * updates 'totalSelected' property.
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
         * Returns selected items on a current page.
         *
         * @returns {Array}
         */
        getPageSelections: function () {
            var ids = this.getIds();

            return this.selected.filter(function (id) {
                return _.contains(ids, id);
            });
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
                excludeMode: this.excludeMode(),
                params: this.getFiltering()
            };
        },

        /**
         * Extracts filtering data from data provider.
         *
         * @returns {Object} Current filters state.
         */
        getFiltering: function () {
            var source = this.source(),
                keys = ['filters', 'search', 'namespace'];

            if (!source) {
                return {};
            }

            return _.pick(source.get('params'), keys);
        },

        /**
         * Defines if provided select/deselect actions is relevant.
         * E.g. there is no need in a 'select page' action if only one
         * page is available.
         *
         * @param {String} actionId - Id of the action to be checked.
         * @returns {Boolean}
         */
        isActionRelevant: function (actionId) {
            var pageIds         = this.getIds().length,
                multiplePages   = pageIds < this.totalRecords(),
                relevant        = true;

            switch (actionId) {
                case 'selectPage':
                    relevant = multiplePages && !this.isPageSelected(true);
                    break;

                case 'deselectPage':
                    relevant =  multiplePages && this.isPageSelected();
                    break;

                case 'selectAll':
                    relevant = !this.allSelected();
                    break;

                case 'deselectAll':
                    relevant = this.totalSelected() > 0;
            }

            return relevant;
        },

        /**
         * Checks if current page has selected records.
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
         *
         * @returns {Multiselect} Chainable.
         */
        updateState: function () {
            var selected        = this.selected().length,
                excluded        = this.excluded().length,
                totalSelected   = this.totalSelected(),
                totalRecords    = this.totalRecords(),
                allSelected     = totalRecords && totalSelected === totalRecords;

            if (this.excludeMode()) {
                if (excluded === totalRecords && !this.preserveSelectionsOnFilter) {
                    this.deselectAll();
                }
            } else if (totalRecords && selected === totalRecords && !this.preserveSelectionsOnFilter) {
                this.selectAll();
            }

            this.allSelected(allSelected);
            this.indetermine(totalSelected && !allSelected);

            return this;
        },

        /**
         * Overrides base method, because this component
         * can't have global field action.
         *
         * @returns {Boolean} False.
         */
        hasFieldAction: function () {
            return false;
        },

        /**
         * Callback method to handle changes of selected items.
         *
         * @param {Array} selected - An array of currently selected items.
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
        },

        /**
         * Is invoked when filtration is applied or removed
         */
        onFilter: function () {
            if (!this.preserveSelectionsOnFilter) {
                this.deselectAll();
            }
        },

        /**
         * Is invoked when search is applied or removed
         */
        onSearch: function () {
            this.onFilter();
        }
    });
});

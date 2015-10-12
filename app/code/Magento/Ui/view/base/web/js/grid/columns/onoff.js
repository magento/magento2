/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/translate',
    './multiselect',
    'uiRegistry'
], function (_, $t, Column, registry) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/onoff',
            bodyTmpl: 'ui/grid/cells/onoff',
            imports: {
                selectedData: '${ $.provider }:data.selectedData'
            },
            listens: {
                '${ $.provider }:reloaded': 'setDefaultSelections'
            }
        },

        /**
         * @param {Integer} id
         * @returns {*}
         */
        getLabel: function (id) {
            return this.selected.indexOf(id) !== -1 ? $t('On') : $t('Off');
        },

        /**
         * Initializes components' static properties.
         *
         * @returns {Column} Chainable.
         */
        initProperties: function () {
            this.actions = [{
                value: 'selectPage',
                label: $t('Select all on this page')
            }, {
                value: 'deselectPage',
                label: $t('Deselect all on this page')
            }];

            return this._super();
        },

        /**
         * Sets the ids for preselected elements
         * @returns void
         */
        setDefaultSelections: function () {
            var positionCacheValid = registry.get('position_cache_valid'),
                key,
                i;

            registry.set('position_cache_valid', true);

            if (this.selected().length === this.selectedData.length || positionCacheValid) {
                return;
            }
            // Check selected data
            for (key in this.selectedData) {
                if (this.selectedData.hasOwnProperty(key) && this.selected().indexOf(key) === -1) {
                    this.selected.push(key);
                }
            }
            // Uncheck unselected data
            for (i = 0; i < this.selected().length; i++) {
                key = this.selected()[i];
                this.selectedData.hasOwnProperty(key) || this.selected.splice(this.selected().indexOf(key), 1);
            }
        },

        /**
         * Show/hide action in the massaction menu
         * @param {Integer} actionId
         * @returns {Boolean}
         */
        isActionRelevant: function (actionId) {
            var relevant = true;

            switch (actionId) {
                case 'selectPage':
                    relevant = !this.isPageSelected(true);
                    break;

                case 'deselectPage':
                    relevant =  this.isPageSelected();
                    break;
            }

            return relevant;
        },

        /**
         * Updates values of the 'allSelected'
         * and 'indetermine' properties.
         *
         * @returns {Multiselect} Chainable.
         */
        updateState: function () {
            var totalRecords    = this.totalRecords(),
                selected        = this.selected().length,
                excluded        = this.excluded().length,
                totalSelected   = this.totalSelected(),
                allSelected;

            // When filters are enabled then totalRecords is unknown
            if (this.getFiltering()) {
                if (this.getFiltering().search !== '') {
                    totalRecords = -1;
                }
            }

            allSelected = totalRecords && totalSelected === totalRecords;

            if (this.excludeMode()) {
                if (excluded === totalRecords) {
                    this.deselectAll();
                }
            } else if (totalRecords && selected === totalRecords) {
                this.selectAll();
            }

            this.allSelected(allSelected);
            this.indetermine(totalSelected && !allSelected);

            return this;
        }
    });
});

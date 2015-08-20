/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/translate',
    './multiselect'
], function (_, $t, Column) {
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
            },
            currentSelectData: []
        },

        getLabel: function(id) {
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
        setDefaultSelections: function() {
            if (this.selected().length != this.selectedData.length) {
                for (var key in this.selectedData) {
                    if (this.selected().indexOf(key) === -1) {
                        this.selected.push(key);
                        this.currentSelectData.push(key);
                    }
                }
                for (var i = 0; i < this.currentSelectData.length; i++) {
                    var removalKey = this.currentSelectData[i];
                    if (!this.selectedData.hasOwnProperty(removalKey) && this.selected().indexOf(removalKey) !== -1) {
                        this.selected.splice(this.selected().indexOf(removalKey), 1);
                        this.currentSelectData.splice(this.currentSelectData.indexOf(removalKey), 1);
                    }
                }
            }
        },

        /**
         * Show/hide action in the massaction menu
         * @param actionId
         * @returns {boolean}
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
            var totalRecords = this.totalRecords();

            // When filters are enabled then totalRecords is unknown
            if (this.getFiltering()) {
                if (this.getFiltering().search !== "") {
                    totalRecords = -1;
                }
            }

            var selected        = this.selected().length,
                excluded        = this.excluded().length,
                totalSelected   = this.totalSelected(),
                allSelected     = totalRecords && totalSelected === totalRecords;

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

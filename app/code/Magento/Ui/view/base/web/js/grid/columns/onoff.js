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
            }
        },

        getLabel: function(id) {
            return (this.selected.indexOf(id) > -1) ? "On" : "Off";
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
            if(this.selected().length == 0) {
                for (var key in this.selectedData) {
                    this.selected.push(key);
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
        }
    });
});

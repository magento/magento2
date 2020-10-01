/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiElement'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/sortBy',
            options: [],
            applied: {},
            sorting: 'asc',
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            selectedOption: '',
            isVisible: true,
            listens: {
                'selectedOption': 'applyChanges'
            },
            statefull: {
                selectedOption: true,
                applied: true
            },
            exports: {
                applied: '${ $.provider }:params.sorting'
            },
            imports: {
                preparedOptions: '${ $.columnsProvider }:elems'
            },
            modules: {
                columns: '${ $.columnsProvider }'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'applied',
                    'selectedOption',
                    'isVisible'
                ]);
        },

        /**
         * Prepared sort order options
         */
        preparedOptions: function (columns) {
            if (columns && columns.length > 0) {
                columns.map(function (column) {
                    if (column.sortable === true) {
                        this.options.push({
                            value: column.index,
                            label: column.label
                        });
                        this.isVisible(true);
                    } else {
                        this.isVisible(false);
                    }
                }.bind(this));
            }
        },

        /**
         * Apply changes
         */
        applyChanges: function () {
            this.applied({
                field: this.selectedOption(),
                direction: this.sorting
            });
        }
    });
});

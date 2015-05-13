/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            sortable: true,
            sorting: false,
            classes: {
                'asc': '_ascend',
                'desc': '_descend'
            },
            links: {
                sorting: '${ $.storageConfig.path }.sorting'
            },
            imports: {
                setSortClass: 'sorting',
                push: 'sorting'
            },
            listens: {
                '${ $.provider }:params.sorting.field': 'onSortChange'
            },
            modules: {
                source: '${ $.provider }'
            }
        },

        initObservable: function () {
            this._super()
                .observe('sorting sortClass');

            return this;
        },

        sort: function (enabled) {
            var direction;

            direction = enabled !== false ?
                this.sorting() ?
                    this.toggleDirection() :
                    'asc' :
                false;

            this.sorting(direction);
        },

        push: function (sorting) {
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

        setSortClass: function (sorting) {
            var sortClass = this.classes[sorting] || '';

            this.sortClass(sortClass);
        },

        onSortChange: function (field) {
            if (field !== this.index) {
                this.sort(false);
            }
        }
    });
});

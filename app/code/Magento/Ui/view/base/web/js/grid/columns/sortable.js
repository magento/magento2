define([
    './column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            sortable: true,
            sorting: false,
            classes: {
                'asc': 'sort-arrow-asc',
                'desc': 'sort-arrow-desc'
            },
            listens: {
                '<%= provider %>:params.sorting.field': 'onSortChange',
                sorting: 'setSortClass push'
            }
        },

        initObservable: function () {
            this._super()
                .observe('sorting sortClass');

            this.setSortClass(this.sorting());

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

            this.source.set('params.sorting', {
                field: this.index,
                direction: sorting
            });

            this.source.reload();
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

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: true,
            sorting: false,
            action: false,
            classes: {
                'asc': 'sort-arrow-asc',
                'desc': 'sort-arrow-desc'
            },

            listens: {
                '<%= provider %>:params.sorting.field': 'onSortUpdate',
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

        applyAction: function () {
            location.href = this.action;
        },

        onSortUpdate: function (field) {
            if (field !== this.index) {
                this.sort(false);
            }
        },

        getLabel: function (data) {
            return data;
        },

        getHeader: function () {
            return this.headerTmpl;
        },

        getBody: function () {
            return this.bodyTmpl;
        }
    });
});

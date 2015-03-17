define([
    'Magento_Ui/js/lib/component/component'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: true,
            sorting: false,
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

        push: function () {
            if (!this.sorting()) {
                return;
            }
            
            this.source.set('params.sorting.field', this.index);
            this.source.set('params.sorting.direction', this.sorting());
        },

        toggleDirection: function () {
            return this.sorting() === 'asc' ?
                'desc' :
                'asc';
        },

        setSortClass: function () {
            var direction = this.sorting(),
                sortClass = this.classes[direction] || '';

            this.sortClass(sortClass);
        },

        onSortUpdate: function (field) {
            if (field !== this.index) {
                this.sort(false);
            }
        },

        getHeader: function () {
            return this.headerTmpl;
        },

        getBody: function () {
            return this.bodyTmpl;
        }
    });
});

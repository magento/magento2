define([
    './sortable'
], function (Sortable) {
    'use strict';

    return Sortable.extend({
        getLabel: function (data) {
            var options = this.options || [],
                label = '';

            options.some(function (item) {
                label = item.label;

                return item.value == data;
            });

            return label;
        }
    });
});

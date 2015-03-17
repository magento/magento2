define([
    './text'
], function (Text) {
    'use strict';

    return Text.extend({
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

define([
    './text'
], function (Text) {
    'use strict';

    return Text.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/select'
        },

        getLabel: function (value) {
            var label = '',
                options = this.options || [];

            options.some(function (item) {
                label = item.label;

                return item.value == value;
            });

            return label;
        }
    });
});

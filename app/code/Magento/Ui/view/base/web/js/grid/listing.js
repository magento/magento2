define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/listing',
            imports: {
                rows: '<%= provider %>:data.items'
            }
        },

        getColspan: function () {
            return this.elems().length;
        },

        hasData: function () {
            return !!this.rows().length;
        }
    });
});

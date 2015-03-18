define([
    'uiComponent',
    'Magento_Ui/js/lib/spinner'
], function (Component, loader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/listing',
            imports: {
                rows: '<%= provider %>:data.items'
            },
            listens: {
                '<%= provider %>:reload': 'showLoader',
                '<%= provider %>:reloaded': 'hideLoader'
            }
        },

        initialize: function () {
            this._super()
                .hideLoader();

            return this;
        },

        hideLoader: function () {
            loader.get(this.name).hide();
        },

        showLoader: function () {
            loader.get(this.name).show();
        },

        getColspan: function () {
            return this.elems().length;
        },

        hasData: function () {
            return !!this.rows().length;
        }
    });
});

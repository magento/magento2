/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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

        getClickUrl: function (row) {
            var field = row[this.action_field],
                action = field && field[this.click_action];

            return action ? action.href : '';
        },

        isClickable: function (row) {
            return !!this.getClickUrl(row);
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
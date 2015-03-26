/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: false
        },

        getClickUrl: function (row) {
            var field = row[this.actionField],
                action = field && field[this.clickAction];

            return action ? action.href : '';
        },

        isClickable: function (row) {
            return !!this.getClickUrl(row);
        },

        redirect: function (url) {
            window.location.href = url;
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

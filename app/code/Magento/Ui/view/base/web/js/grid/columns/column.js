/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiComponent'
], function (_, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: false,
            visible: true,
            links: {
                visible: '${ $.storageConfig.path }.visible'
            }
        },

        initObservable: function () {
            this._super()
                .observe('visible');

            return this;
        },

        applyState: function (property, state) {
            var storage = this.storage(),
                namespace = this.storageConfig.root + '.' + property,
                data,
                value;

            if (state === 'default') {
                data = storage.getDefault();
            } else if (state === 'last') {
                data = storage.getSaved();
            }

            value = utils.nested(data, namespace);

            if (!_.isUndefined(value)) {
                this.set(property, value);
            }
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

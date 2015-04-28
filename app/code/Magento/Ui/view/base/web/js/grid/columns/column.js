/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'uiComponent'
], function (utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'ui/grid/cells/text',
            sortable: false,
            visible: true,
            states: {
                provider: '',
                namespace: 'columns.<%= index %>',
                data: {
                    visible: '<%= states.namespace %>.visible'
                }
            },
            links: {
                visible: '<%= states.provider %>:current.<%= states.data.visible %>'
            },
            modules: {
                statesProvider: '<%= states.provider %>'
            }
        },

        initObservable: function () {
            this._super()
                .observe('visible');

            return this;
        },

        applyState: function (property, state) {
            var provider = this.statesProvider(),
                namespace = this.states.data[property],
                data,
                value;

            if (state === 'default') {
                data = provider.getDefault();
            } else if (state === 'last') {
                data = provider.getSaved();
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

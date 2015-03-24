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
            template: 'ui/grid/actions',
            actionsVisible: false,
            noItems:  'You haven\'t selected any items!'
        },

        initObservable: function () {
            this._super()
                .observe('actionsVisible');

            return this;
        },

        applyAction: function (action) {
            var proceed = true,
                data = this.source.get('config.multiselect');

            if (!data || !data.totalSelected) {
                proceed = false;

                alert(this.noItems);
            }

            if (proceed && action.confirm) {
                proceed = window.confirm(action.confirm);
            }

            if (proceed) {
                utils.submit({
                    url: action.url,
                    data: data
                });
            }
        },

        toggleActions: function () {
            this.actionsVisible(!this.actionsVisible());
        },

        hideActions: function () {
            this.actionsVisible(false);
        }
    });
});

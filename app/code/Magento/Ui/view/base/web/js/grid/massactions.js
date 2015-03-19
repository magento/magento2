/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiComponent'
], function (_, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/actions',
            actionsVisible: false
        },

        initObservable: function () {
            this._super()
                .observe('actionsVisible');

            return this;
        },

        applyAction: function (action) {
            var confirmed = true;

            if (action.confirm) {
                confirmed = window.confirm(action.confirm);
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

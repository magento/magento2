/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Variables, updateElementAtCursor */
define([
    'uiComponent',
    'uiRegistry'
], function (UiComponent, registry) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            buttons: []
        },

        /**
         * Return whether we have buttons or not.
         *
         * @return {Boolean}
         */
        hasButtons: function () {
            return this.buttons.length > 0;
        },

        /**
         * Triggers some method in every modal child elem, if this method is defined.
         *
         * @param {Object} action - action configuration,
         * must contain actionName and targetName and
         * can contain params
         */
        triggerAction: function (action) {
            var targetName = action.targetName,
                params = action.params || [],
                actionName = action.actionName,
                target;

            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                params.unshift(actionName);
                target.apply(target, params);
            }
        }
    });
});

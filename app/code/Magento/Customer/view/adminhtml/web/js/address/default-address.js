/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button'
], function (Button) {
    'use strict';

    return Button.extend({
        defaults: {
            entityId: null,
            parentId: null
        },

        /**
         * Initializes component.
         *
         * @returns {Button}
         */
        initialize: function () {
            this._super();
            if (!this.parentId) {
                this.visible(this.entityId);
            }

            return this;
        },

        /**
         * Apply action on target component,
         * but previously create this component from template if it is not existed
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            if (action.params && action.params[0]) {
                action.params[0].entityId = this.entityId;
                action.params[0].parentId = this.parentId;
            } else {
                action.params = [{
                    entityId: this.entityId,
                    parentId: this.parentId
                }];
            }

            this._super();
        }
    });
});

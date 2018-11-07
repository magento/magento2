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
            entity_id: null,
            parent_id: null
        },

        /**
         * Initializes component.
         *
         * @returns {Button}
         */
        initialize: function () {
            this._super();
            if (!this.parent_id) {
                this.visible(this.entity_id);
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
                action.params[0].entity_id = this.entity_id;
                action.params[0].parent_id = this.parent_id;
            } else {
                action.params = [{
                    entity_id: this.entity_id,
                    parent_id: this.parent_id
                }];
            }

            this._super();
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox-toggle-notice'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            imports: {
                toggleDisabled: '${ $.parentName }.simple_action:value'
            }
        },

        /**
         * Toggle element disabled state according to simple action value.
         *
         * @param {String} action
         */
        toggleDisabled: function (action) {
            switch (action) {
                default:
                    this.disabled(false);
            }

            if (this.disabled()) {
                this.checked(false);
            }
        }
    });
});

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button'
], function (Button) {
    'use strict';

    return Button.extend({
        defaults: {
            imports: {
                handleAccessibility: '${ $.controlCheckbox }'
            }
        },

        /**
         * Handle button accessibility based on control state.
         *
         * @param {Boolean} checkboxState
         */
        handleAccessibility: function (checkboxState) {
            this.disabled(!checkboxState);
        }
    });
});

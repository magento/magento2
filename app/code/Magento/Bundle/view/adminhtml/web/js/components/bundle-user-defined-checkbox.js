/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            listens: {
                inputType: 'onInputTypeChange'
            }
        },

        /**
         * Handler for "inputType" property
         *
         * @param {String} data
         */
        onInputTypeChange: function (data) {
            data === 'checkbox' || data === 'multi' ?
                this.clear()
                    .visible(false) :
                this.visible(true);
        }
    });
});

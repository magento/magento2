/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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

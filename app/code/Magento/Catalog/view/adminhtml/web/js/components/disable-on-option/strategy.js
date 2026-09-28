/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
define(function () {
    'use strict';

    return {
        defaults: {
            valuesForEnable: [],
            disabled: true,
            imports: {
                toggleDisable:
                    'product_attribute_add_form.product_attribute_add_form.base_fieldset.frontend_input:value'
            }
        },

        /**
         * Toggle disabled state.
         *
         * @param {Number} selected
         */
        toggleDisable: function (selected) {
            this.disabled(!(selected in this.valuesForEnable));
        }
    };
});

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    return {
        defaults: {
            valuesForEnable: [],
            disabled: true,
            imports: {
                toggleDisable:
                    'product_attribute_add_form.product_attribute_add_form.base_fieldset.frontend_input:value'
            }
        },

        toggleDisable: function (selected) {
            this.disabled(!(selected in this.valuesForEnable));
        }
    }
});

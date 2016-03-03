/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    return {
        defaults: {
            valuesForOptions: [],
            visibilityState: true,
            imports: {
                toggleVisibility:
                    'product_attribute_add_form.product_attribute_add_form.base_fieldset.frontend_input:value'
            },
            isShown: false
        },

        initElement: function (item) {
            this._super();
            item.set('visible', this.visibilityState);
            return this;
        },

        toggleVisibility: function (selected) {
            this.isShown = this.visibilityState = selected in this.valuesForOptions;
            this.visible(this.isShown);
        }
    }
});

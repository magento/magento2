/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    return {
        defaults: {
            valuesForOptions: [],
            imports: {
                toggleVisibility:
                    'product_attribute_add_form.product_attribute_add_form.base_fieldset.frontend_input:value'
            },
            isShown: false,
            inverseVisibility: false
        },

        /**
         * Initialize item.
         *
         * @param {Object} item
         * @returns {Object} Chainable.
         */
        initElement: function (item) {
            this._super();
            item.set('visible', this.inverseVisibility ? !this.isShown : this.isShown);
            
            return this;
        },

        /**
         * Toggle visibility state.
         *
         * @param {Number} selected
         */
        toggleVisibility: function (selected) {
            this.isShown = selected in this.valuesForOptions;
            this.visible(this.inverseVisibility ? !this.isShown : this.isShown);
        }
    };
});

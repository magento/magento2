/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/fieldset',
    'uiRegistry'
], function (Component, uiRegistry) {
    'use strict';

    return Component.extend({
        /**
         * Handler of the "opened" property changes.
         *
         * @param {Boolean} isOpened
         */
        onVisibilityChange: function (isOpened) {
            this._super(isOpened);

            // Once the Custom Options tab is opened change "affect_product_custom_options" value to 1
            // in order custom options be processed on backend.
            if (this.opened()) {
                uiRegistry.get('product_form.product_form.custom_options.affect_product_custom_options', function(component) {
                    component.value(1);
                });
            }
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            imports: {
                handleUseDefault: '${ $.parentName }.use_default.url_key:checked',
                urlKey: '${ $.provider }:data.url_key'
            },
            listens: {
                urlKey: 'handleChanges'
            },
            modules: {
                useDefault: '${ $.parentName }.use_default.url_key'
            }
        },

        /**
         * Disable checkbox field, when 'url_key' field without changes or 'use default' field is checked
         */
        handleChanges: function (newValue) {
            this.disabled(newValue === this.valueMap['true'] || this.useDefault.checked);
        },

        /**
         * Disable checkbox field, when 'url_key' field without changes or 'use default' field is checked
         */
        handleUseDefault: function (checkedUseDefault) {
            this.disabled(this.urlKey === this.valueMap['true'] || checkedUseDefault);
        }
    });
});

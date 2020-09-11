/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    './select',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function (_, registry, Select, defaultPostCodeResolver) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                countryOptions: '${ $.parentName }.country_id:indexedOptions',
                update: '${ $.parentName }.country_id:value'
            }
        },

        /**
         * Method called every time country selector's value gets changed.
         * Updates all validations and requirements for certain country.
         * @param {String} value - Selected country ID.
         */
        update: function (value) {
            var isRegionRequired,
                option;

            if (!value) {
                return;
            }

            option = _.isObject(this.countryOptions) && this.countryOptions[value];

            if (!option) {
                return;
            }

            defaultPostCodeResolver.setUseDefaultPostCode(!option['is_zipcode_optional']);

            if (option['is_region_visible'] === false) {
                // Hide select and corresponding text input field if region must not be shown for selected country.
                this.setVisible(false);

                if (this.customEntry) { // eslint-disable-line max-depth
                    this.toggleInput(false);
                }
            }

            isRegionRequired = !this.skipValidation && !!option['is_region_required'];

            if (!isRegionRequired) {
                this.error(false);
            }

            this.required(isRegionRequired);
            this.validation['required-entry'] = isRegionRequired;

            registry.get(this.customName, function (input) {
                input.required(isRegionRequired);
                input.validation['required-entry'] = isRegionRequired;
                input.validation['validate-not-number-first'] = !this.options().length;
            }.bind(this));
        }
    });
});


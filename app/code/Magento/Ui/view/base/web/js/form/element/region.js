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
         * {@inheritdoc}
         */
        initialize: function () {
            var option;

            this._super();

            option = _.find(this.countryOptions, function (row) {
                return row['is_default'] === true;
            });
            this.hideRegion(option);

            return this;
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

            this.hideRegion(option);

            defaultPostCodeResolver.setUseDefaultPostCode(!option['is_zipcode_optional']);

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
        },

        /**
         * Hide select and corresponding text input field if region must not be shown for selected country.
         *
         * @private
         * @param {Object}option
         */
        hideRegion: function (option) {
            if (!option || option['is_region_visible'] !== false) {
                return;
            }

            this.setVisible(false);

            if (this.customEntry) {
                this.toggleInput(false);
            }
        }
    });
});

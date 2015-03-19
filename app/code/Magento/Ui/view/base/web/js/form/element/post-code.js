/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/registry/registry',
    './abstract'
], function (_, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        /**
         * Extended list of Listeners
         *
         * @return {this}
         */
        initListeners: function () {
            this._super()
                .update()
                .provider.data.on('update:' + this.parentScope + '.country_id', this.update.bind(this));

            return this;
        },

        /**
         * Fix _postcode_ depend on _country_id_ change:
         *  - If country in list "Zip/Postal Code is Optional countries" then
         *    - field "postcode" should not be required
         *
         * @returns {this}
         */
        update: function () {
            var parentScope = this.getPart(this.getPart(this.name, -2), -2),
                option,
                postcode = this;

            registry.get(parentScope + '.country_id.0', function (countryComponent) {
                var value = countryComponent.value();

                if (!value) { // empty value discard logic
                    return;
                }

                countryComponent
                    .options()
                    .some(function (el) {
                        option = el;

                        return el.value === value;
                    });

                if (!option.is_region_required) {
                    postcode.error(false);
                    postcode.validation = _.omit(postcode.validation, 'required-entry');
                } else {
                    postcode.validation['required-entry'] = true;
                }
                postcode.required(!!option.is_region_required);
            });

            return this;
        }
    });
});

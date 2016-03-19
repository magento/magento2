/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    './select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                update: '${ $.parentName }.country_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            var country = registry.get(this.parentName + '.' + 'country_id'),
                options = country.indexedOptions,
                option;

            if (!value) {
                return;
            }

            option = options[value];

            if (!option['is_region_required']) {
                this.error(false);
                this.validation = _.omit(this.validation, 'required-entry');
            } else {
                this.validation['required-entry'] = true;
            }

            this.required(!!option['is_region_required']);
        }
    });
});

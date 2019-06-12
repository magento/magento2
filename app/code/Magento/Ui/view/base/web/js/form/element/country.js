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
    './select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                update: '${ $.parentName }.website_id:value'
            }
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var result, defaultCountry, defaultValue;

            if (!field) { //validate field, if we are on update
                field = this.filterBy.field;
            }

            this._super(value, field);
            result = _.filter(this.initialOptions, function (item) {

                if (item[field]) {
                    return ~item[field].indexOf(value);
                }

                return false;
            });

            this.setOptions(result);
            this.reset();

            if (!this.value()) {
                defaultCountry = _.filter(result, function (item) {
<<<<<<< HEAD
                    return item['is_default'] && item['is_default'].includes(value);
=======
                    return item['is_default'] && _.contains(item['is_default'], value);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                });

                if (defaultCountry.length) {
                    defaultValue = defaultCountry.shift();
                    this.value(defaultValue.value);
                }
            }
        }
    });
});


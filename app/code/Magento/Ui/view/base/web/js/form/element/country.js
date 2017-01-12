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
            var result;

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
        }
    });
});


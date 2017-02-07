/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mageUtils',
    '../shipping-rates-validation-rules/tablerate',
    'mage/translate'
], function ($, utils, validationRules, $t) {
    'use strict';

    return {
        validationErrors: [],

        /**
         * @param {Object} address
         * @return {Boolean}
         */
        validate: function (address) {
            var self = this;

            this.validationErrors = [];
            $.each(validationRules.getRules(), function (field, rule) {
                var message, regionFields;

                if (rule.required && utils.isEmpty(address[field])) {
                    message = $t('Field ') + field + $t(' is required.');
                    regionFields = ['region', 'region_id', 'region_id_input'];

                    if (
                        $.inArray(field, regionFields) === -1 ||
                        utils.isEmpty(address.region) && utils.isEmpty(address['region_id'])
                    ) {
                        self.validationErrors.push(message);
                    }
                }
            });

            return !Boolean(this.validationErrors.length);
        }
    };
});

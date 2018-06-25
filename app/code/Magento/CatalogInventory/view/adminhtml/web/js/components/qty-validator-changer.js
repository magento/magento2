/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            valueUpdate: 'input'
        },

        /**
         * Change validator
         */
        handleChanges: function (value) {
            var isDigits = value !== 1;

            this.validation['validate-integer'] = isDigits;
            this.validation['validate-digits'] = isDigits;
            this.validation['less-than-equals-to'] = isDigits ? 99999999 : 99999999.9999;
            this.validate();
        },

        /**
         * Change input value type when "Enable Qty Increments" is "No"
         */
        changeValueType: function (value) {
            var isEnableQtyIncrements = value === 1;

            if (!isEnableQtyIncrements) {
                if (this.value().length) {
                    this.value(parseInt(this.value()));
                }
            }
        }
    });
});

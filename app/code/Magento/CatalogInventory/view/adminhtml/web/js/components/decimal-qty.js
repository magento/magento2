/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            numberValidator: {
                'validate-number': true
            },
            digitsValidator: {
                'validate-digits': true
            },
            listens: {
                value: 'changeValidation'
            },
            modules: {
                quantityIncrements: '${ $.quantityIncrements }',
                minSaleQuantity: '${ $.minSaleQuantity }'
            }
        },

        /**
         * Change validation rules for another components based on current value.
         */
        changeValidation: function () {
            var validator;

            if (parseFloat(this.value())) {
                validator = this.numberValidator;
            } else {
                validator = this.digitsValidator;
            }

            this.quantityIncrements().validation = validator;
            this.minSaleQuantity().validation = validator;
        }
    });
});

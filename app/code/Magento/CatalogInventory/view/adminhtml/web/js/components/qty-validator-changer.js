/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            numberValidator: {
                'validate-number': true
            },
            digitsValidator: {
                'validate-digits': true
            },
            valueUpdate: 'input'
        },

        /**
         * Change validator
         */
        handleChanges: function (value) {
            if (value === 1) {
                this.validation = this.numberValidator;
                this.validate();
            } else {
                this.validation = this.digitsValidator;
                this.validate();
            }
        }
    });
});

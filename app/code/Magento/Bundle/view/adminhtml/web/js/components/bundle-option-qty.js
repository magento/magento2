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
            valueUpdate: 'input',
            isInteger: true,
            validation: {
                'validate-number': true,
                'validate-digits': true
            }
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            this.validation['validate-digits'] = this.isInteger;
            this._super();
        },

        /**
         * @inheritdoc
         */
        hasChanged: function () {
            var notEqual = this.value() !== this.initialValue.toString();

            return !this.visible() ? false : notEqual;
        },

        /**
         * Update field validation rules
         *
         * @param {Boolean} value
         */
        validateDigits: function (value) {
            this.isInteger = value;
            this.validation['validate-digits'] = this.isInteger;
        }

    });
});

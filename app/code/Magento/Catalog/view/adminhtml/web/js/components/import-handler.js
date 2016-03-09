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
            allowImport: true,
            autoImportIfEmpty: false,
            nameValue: '',
            valueUpdate: 'input'
        },

        /**
         * Import value, if it's allowed
         */
        handleChanges: function (newValue) {
            this.nameValue = newValue;

            if (this.allowImport) {
                this.value(newValue);
            }
        },

        /**
         * Disallow import when initial value isn't empty string
         *
         * @returns {*}
         */
        setInitialValue: function () {
            this._super();

            if (this.initialValue !== '') {
                this.allowImport = false;
            }

            return this;
        },

        /**
         *  Callback when value is changed by user,
         *  and disallow/allow import value
         */
        userChanges: function () {
            this._super();

            if (this.value() === '') {
                this.allowImport = true;

                if (this.autoImportIfEmpty) {
                    this.value(this.nameValue);
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});

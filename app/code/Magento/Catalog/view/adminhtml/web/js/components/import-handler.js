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
            values: {
                'name': '',
                'description': '',
                'sku': '',
                'color': '',
                'country_of_manufacture': '',
                'gender': '',
                'material': '',
                'short_description': '',
                'size': ''
            },
            valueUpdate: 'input',
            mask: ''
        },

        handleNameChanges: function (newValue) {
            this.values.name = newValue;
            this.updateValue();
        },

        handleDescriptionChanges: function (newValue) {
            this.values.description = newValue;
            this.updateValue();
        },

        handleSkuChanges: function (newValue) {
            if (this.code !== 'sku') {
                this.values.sku = newValue;
                this.updateValue();
            }
        },

        handleColorChanges: function (newValue) {
            this.values.color = newValue;
            this.updateValue();
        },

        handleCountryChanges: function (newValue) {
            this.values.country = newValue;
            this.updateValue();
        },

        handleGenderChanges: function (newValue) {
            this.values.gender = newValue;
            this.updateValue();
        },

        handleMaterialChanges: function (newValue) {
            this.values.material = newValue;
            this.updateValue();
        },

        handleShortDescriptionChanges: function (newValue) {
            this.values.short_description = newValue;
            this.updateValue();
        },

        handleSizeChanges: function (newValue) {
            this.values.size = newValue;
            this.updateValue();
        },

        updateValue: function () {
            var str = this.mask;
            var nonEmptyValueFlag = false;
            var placeholder;

            if (!this.allowImport) {
                return;
            }

            for (var property in this.values) {
                if (this.values.hasOwnProperty(property)) {
                    placeholder = '';
                    placeholder = placeholder.concat('{{', property, '}}');
                    str = str.replace(placeholder, this.values[property]);
                    nonEmptyValueFlag = nonEmptyValueFlag || !!this.values[property];
                }
            }
           // strip tags
            var tmp = document.createElement("div");
            tmp.innerHTML = str;
            str =  tmp.textContent || tmp.innerText || "";

            if (nonEmptyValueFlag) {
                this.value(str);
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
                    this.value(this.updateValue());
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});

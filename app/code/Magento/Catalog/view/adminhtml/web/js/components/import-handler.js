/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/textarea'
], function (Textarea) {
    'use strict';

    return Textarea.extend({
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

        /**
         * Handle name value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleNameChanges: function (newValue) {
            this.values.name = newValue;
            this.updateValue();
        },

        /**
         * Handle description value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleDescriptionChanges: function (newValue) {
            this.values.description = newValue;
            this.updateValue();
        },

        /**
         * Handle sku value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleSkuChanges: function (newValue) {
            if (this.code !== 'sku') {
                this.values.sku = newValue;
                this.updateValue();
            }
        },

        /**
         * Handle color value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleColorChanges: function (newValue) {
            this.values.color = newValue;
            this.updateValue();
        },

        /**
         * Handle country value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleCountryChanges: function (newValue) {
            this.values.country = newValue;
            this.updateValue();
        },

        /**
         * Handle gender value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleGenderChanges: function (newValue) {
            this.values.gender = newValue;
            this.updateValue();
        },

        /**
         * Handle material value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleMaterialChanges: function (newValue) {
            this.values.material = newValue;
            this.updateValue();
        },

        /**
         * Handle short description value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleShortDescriptionChanges: function (newValue) {
            this.values['short_description'] = newValue;
            this.updateValue();
        },

        /**
         * Handle size value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleSizeChanges: function (newValue) {
            this.values.size = newValue;
            this.updateValue();
        },

        /**
         * Update field value, if it's allowed
         */
        updateValue: function () {
            var str = this.mask,
                nonEmptyValueFlag = false,
                placeholder,
                property,
                tmpElement;

            if (!this.allowImport) {
                return;
            }

            for (property in this.values) {
                if (this.values.hasOwnProperty(property)) {
                    placeholder = '';
                    placeholder = placeholder.concat('{{', property, '}}');
                    str = str.replace(placeholder, this.values[property]);
                    nonEmptyValueFlag = nonEmptyValueFlag || !!this.values[property];
                }
            }
            // strip tags
            tmpElement = document.createElement('div');
            tmpElement.innerHTML = str;
            str =  tmpElement.textContent || tmpElement.innerText || '';

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
                    this.updateValue();
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});

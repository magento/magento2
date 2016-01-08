/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'underscore'
], function (AbstractField, _) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            template: 'ui/form/components/single/field',
            checked: false,
            exportedValue: '',
            isMultiple: false,
            preferCheckbox: true,
            valueMap: {},
            keyboard: {},

            templates: {
                radio: 'ui/form/components/single/radio',
                checkbox: 'ui/form/components/single/checkbox'
            },

            listens: {
                'checked': 'onCheckedChanged',
                'value': 'onValueChanged',
                'exportedValue': 'onExtendedValueChanged'
            },
            links: {
                value: false,
                exportedValue: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * @returns {Element}
         */
        initialize: function () {
            this._super();
            this.initKeyboardHandlers();
            this.elementTmpl = this.isMultiple || this.preferCheckbox ?
                this.templates.checkbox :
                this.templates.radio;

            return this;
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            if (this.isMultiple && !_.isArray(this.exportedValue) && _.isEmpty(this.exportedValue)) {
                this.exportedValue = [];
            }

            return this._super()
                .observe('checked exportedValue');
        },

        /**
         * Initialize keyboard handlers
         * @returns {Element}
         */
        initKeyboardHandlers: function () {
            _.bindAll(this, 'goToPrevious', 'goToNext');
            _.extend(this.keyboard, {
                37: this.goToPrevious, // Left arrow
                38: this.goToPrevious, // Up arrow
                39: this.goToNext,     // Right arrow
                40: this.goToNext      // down arrow
            });

            return this;
        },

        /**
         * (Should) Move focus to previous <checkbox>
         * @param {jQuery.Event} event
         */
        goToPrevious: function (event) {
            event.preventDefault();
        },

        /**
         * (Should) Move focus to next <checkbox>
         * @param {jQuery.Event} event
         */
        goToNext: function (event) {
            event.preventDefault();
        },

        /**
         * Get true/false key from valueMap by value.
         *
         * @param {*} value
         * @returns {Boolean|undefined}
         */
        getReverseValueMap: function getReverseValueMap(value) {
            var bool;

            _.some(this.valueMap, function (iValue, iBool) {
                if (iValue === value) {
                    bool = iBool === 'true';

                    return true;
                }
            });

            return bool;
        },

        /**
         * Handle value changes for checkbox / radio button.
         *
         * @param {*} updatedValue
         */
        onValueChanged: function (updatedValue) {
            var oldChecked = this.checked.peek(),
                isMappedUsed = !_.isEmpty(this.valueMap),
                newChecked = false;

            if (isMappedUsed) {
                newChecked = this.getReverseValueMap(updatedValue);
            } else if (typeof updatedValue === 'boolean') {
                newChecked = updatedValue;
            }

            if (newChecked !== oldChecked) {
                this.checked(newChecked);
            }
        },

        /**
         * Handle dataScope changes for checkbox / radio button.
         *
         * @param {*} newExportedValue
         */
        onExtendedValueChanged: function (newExportedValue) {
            var isMappedUsed = !_.isEmpty(this.valueMap),
                oldChecked = this.checked.peek(),
                oldValue = this.value.peek(),
                newChecked;

            if (this.isMultiple) {
                newChecked = newExportedValue.indexOf(oldValue) !== -1;
            } else if (isMappedUsed) {
                newChecked = this.getReverseValueMap(newExportedValue);
            } else {
                newChecked = newExportedValue === oldValue;
            }

            if (newChecked !== oldChecked) {
                this.checked(newChecked);
            }
        },

        /**
         * Handle checked state changes for checkbox / radio button.
         *
         * @param {Boolean} newChecked
         */
        onCheckedChanged: function (newChecked) {
            var isMappedUsed = !_.isEmpty(this.valueMap),
                oldValue = this.value.peek(),
                newValue;

            if (isMappedUsed) {
                newValue = this.valueMap[newChecked];
            } else {
                newValue = oldValue;
            }

            if (!this.isMultiple && newChecked) {
                this.exportedValue(newValue);
            } else if (!this.isMultiple && !newChecked) {
                if (typeof newValue === 'boolean') {
                    this.exportedValue(newChecked);
                } else if (newValue === this.exportedValue.peek()) {
                    this.exportedValue('');
                }

                if (isMappedUsed) {
                    this.exportedValue(newValue);
                }
            } else if (this.isMultiple && newChecked) {
                if (this.exportedValue.indexOf(newValue) === -1) {
                    this.exportedValue.push(newValue);
                }
            } else if (this.isMultiple && !newChecked) {
                if (this.exportedValue.indexOf(newValue) !== -1) {
                    this.exportedValue.splice(this.exportedValue.indexOf(newValue), 1);
                }
            }
        }
    });
});

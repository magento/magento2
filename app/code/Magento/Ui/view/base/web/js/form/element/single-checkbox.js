/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'underscore',
    'mage/translate'
], function (AbstractField, _, $t) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            template: 'ui/form/components/single/field',
            checked: false,
            isMultiple: false,
            prefer: 'checkbox', // 'radio' | 'checkbox' | 'toggle'
            valueMap: {},
            keyboard: {},

            templates: {
                radio: 'ui/form/components/single/radio',
                checkbox: 'ui/form/components/single/checkbox',
                toggle: 'ui/form/components/single/switcher'
            },

            listens: {
                'checked': 'onCheckedChanged',
                'value': 'onExtendedValueChanged'
            }
        },

        /**
         * @returns {Element}
         */
        initialize: function () {
            return this
                ._super()
                .initKeyboardHandlers();
        },

        /**
         * @returns {Element}
         */
        initConfig: function (config) {
            this._super();

            if (!config.elementTmpl) {
                if (!this.prefer && !this.isMultiple) {
                    this.elementTmpl = this.templates.radio;
                } else if (this.prefer === 'radio') {
                    this.elementTmpl = this.templates.radio;
                } else if (this.prefer === 'checkbox') {
                    this.elementTmpl = this.templates.checkbox;
                } else if (this.prefer === 'toggle') {
                    this.elementTmpl = this.templates.toggle;
                } else {
                    this.elementTmpl = this.templates.checkbox;
                }
            }

            if (this.prefer === 'toggle' && _.isEmpty(this.toggleLabels)) {
                this.toggleLabels = {
                    'on': $t('Yes'),
                    'off': $t('No')
                };
            }

            if (typeof this.default === 'undefined' || this.default === null) {
                this.default = '';
            }

            if (typeof this.value === 'undefined' || this.value === null) {
                this.value = _.isEmpty(this.valueMap) || this.default !== '' ? this.default : this.valueMap.false;
                this.initialValue = this.value;
            } else {
                this.initialValue = this.value;
            }

            if (this.isMultiple && !_.isArray(this.value)) {
                this.value = []; // needed for correct observable assingment
            }

            return this;
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
                ._super()
                .observe('checked');
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
            var bool = false;

            _.some(this.valueMap, function (iValue, iBool) {
                if (iValue === value) {
                    bool = iBool === 'true';

                    return true;
                }
            });

            return bool;
        },

        /**
         * @returns {Element}
         */
        setInitialValue: function () {
            if (_.isEmpty(this.valueMap)) {
                this.on('value', this.onUpdate.bind(this));
            } else {
                this._super();
                this.checked(this.getReverseValueMap(this.value()));
            }

            return this;
        },

        /**
         * Handle dataScope changes for checkbox / radio button.
         *
         * @param {*} newExportedValue
         */
        onExtendedValueChanged: function (newExportedValue) {
            var isMappedUsed = !_.isEmpty(this.valueMap),
                oldChecked = this.checked.peek(),
                oldValue = this.initialValue,
                newChecked;

            if (this.isMultiple) {
                newChecked = newExportedValue.indexOf(oldValue) !== -1;
            } else if (isMappedUsed) {
                newChecked = this.getReverseValueMap(newExportedValue);
            } else if (typeof newExportedValue === 'boolean') {
                newChecked = newExportedValue;
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
                oldValue = this.initialValue,
                newValue;

            if (isMappedUsed) {
                newValue = this.valueMap[newChecked];
            } else {
                newValue = oldValue;
            }

            if (!this.isMultiple && newChecked) {
                this.value(newValue);
            } else if (!this.isMultiple && !newChecked) {
                if (typeof newValue === 'boolean') {
                    this.value(newChecked);
                } else if (newValue === this.value.peek()) {
                    this.value('');
                }

                if (isMappedUsed) {
                    this.value(newValue);
                }
            } else if (this.isMultiple && newChecked && this.value.indexOf(newValue) === -1) {
                this.value.push(newValue);
            } else if (this.isMultiple && !newChecked && this.value.indexOf(newValue) !== -1) {
                this.value.splice(this.value.indexOf(newValue), 1);
            }
        },

        /**
         * @returns {Element}
         */
        onUpdate: function () {
            if (this.hasUnique) {
                this.setUnique();
            }

            return this._super();
        }
    });
});

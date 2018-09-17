/**
 * Copyright © Magento, Inc. All rights reserved.
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
            initialChecked: false,
            multiple: false,
            prefer: 'checkbox', // 'radio' | 'checkbox' | 'toggle'
            valueMap: {},

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
         * @inheritdoc
         */
        initConfig: function (config) {
            this._super();

            if (!config.elementTmpl) {
                if (!this.prefer && !this.multiple) {
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

            if (this.multiple && !_.isArray(this.value)) {
                this.value = []; // needed for correct observable assignment
            }

            this.initialChecked = this.checked;

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this
                ._super()
                .observe('checked');
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
         * @inheritdoc
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

            if (this.multiple) {
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

            if (!this.multiple && newChecked) {
                this.value(newValue);
            } else if (!this.multiple && !newChecked) {
                if (typeof newValue === 'boolean') {
                    this.value(newChecked);
                } else if (newValue === this.value.peek()) {
                    this.value('');
                }

                if (isMappedUsed) {
                    this.value(newValue);
                }
            } else if (this.multiple && newChecked && this.value.indexOf(newValue) === -1) {
                this.value.push(newValue);
            } else if (this.multiple && !newChecked && this.value.indexOf(newValue) !== -1) {
                this.value.splice(this.value.indexOf(newValue), 1);
            }
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            if (this.hasUnique) {
                this.setUnique();
            }

            return this._super();
        },

        /**
         * @inheritdoc
         */
        reset: function () {
            if (this.multiple && this.initialChecked) {
                this.value.push(this.initialValue);
            } else if (this.multiple && !this.initialChecked) {
                this.value.splice(this.value.indexOf(this.initialValue), 1);
            } else {
                this.value(this.initialValue);
            }

            this.error(false);

            return this;
        },

        /**
         * @inheritdoc
         */
        clear: function () {
            if (this.multiple) {
                this.value([]);
            } else {
                this.value('');
            }

            this.error(false);

            return this;
        }
    });
});

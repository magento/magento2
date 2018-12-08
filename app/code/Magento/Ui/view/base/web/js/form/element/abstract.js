/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiElement',
    'Magento_Ui/js/lib/validation/validator'
], function (_, utils, layout, Element, validator) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            preview: '',
            focused: false,
            required: false,
            disabled: false,
            valueChangedByUser: false,
            elementTmpl: 'ui/form/element/input',
            tooltipTpl: 'ui/form/element/helper/tooltip',
            fallbackResetTpl: 'ui/form/element/helper/fallback-reset',
            'input_type': 'input',
            placeholder: false,
            description: '',
            labelVisible: true,
            label: '',
            error: '',
            warn: '',
            notice: '',
            customScope: '',
            default: '',
            isDifferedFromDefault: false,
            showFallbackReset: false,
            additionalClasses: {},
            isUseDefault: '',
            serviceDisabled: false,
            valueUpdate: false, // ko binding valueUpdate

            switcherConfig: {
                component: 'Magento_Ui/js/form/switcher',
                name: '${ $.name }_switcher',
                target: '${ $.name }',
                property: 'value'
            },
            listens: {
                visible: 'setPreview',
                value: 'setDifferedFromDefault',
                '${ $.provider }:data.reset': 'reset',
                '${ $.provider }:data.overload': 'overload',
                '${ $.provider }:${ $.customScope ? $.customScope + "." : ""}data.validate': 'validate',
                'isUseDefault': 'toggleUseDefault'
            },
            ignoreTmpls: {
                value: true
            },

            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            _.bindAll(this, 'reset');

            this._super()
                .setInitialValue()
                ._setClasses()
                .initSwitcher();

            return this;
        },

        /**
         * Checks if component has error.
         *
         * @returns {Object}
         */
        checkInvalid: function () {
            return this.error() && this.error().length ? this : null;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            var rules = this.validation = this.validation || {};

            this._super();

            this.observe('error disabled focused preview visible value warn notice isDifferedFromDefault')
                .observe('isUseDefault serviceDisabled')
                .observe({
                    'required': !!rules['required-entry']
                });

            return this;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Abstract} Chainable.
         */
        initConfig: function () {
            var uid = utils.uniqueid(),
                name,
                valueUpdate,
                scope;

            this._super();

            scope = this.dataScope.split('.');
            name = scope.length > 1 ? scope.slice(1) : scope;

            valueUpdate = this.showFallbackReset ? 'afterkeydown' : this.valueUpdate;

            _.extend(this, {
                uid: uid,
                noticeId: 'notice-' + uid,
                errorId: 'error-' + uid,
                inputName: utils.serializeName(name.join('.')),
                valueUpdate: valueUpdate
            });

            return this;
        },

        /**
         * Initializes switcher element instance.
         *
         * @returns {Abstract} Chainable.
         */
        initSwitcher: function () {
            if (this.switcherConfig.enabled) {
                layout([this.switcherConfig]);
            }

            return this;
        },

        /**
         * Sets initial value of the element and subscribes to it's changes.
         *
         * @returns {Abstract} Chainable.
         */
        setInitialValue: function () {
            this.initialValue = this.getInitialValue();

            if (this.value.peek() !== this.initialValue) {
                this.value(this.initialValue);
            }

            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            return this;
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Abstract} Chainable.
         */
        _setClasses: function () {
            var additional = this.additionalClasses;

            if (_.isString(additional)) {
                this.additionalClasses = {};

                if (additional.trim().length) {
                    additional = additional.trim().split(' ');

                    additional.forEach(function (name) {
                        if (name.length) {
                            this.additionalClasses[name] = true;
                        }
                    }, this);
                }
            }

            _.extend(this.additionalClasses, {
                _required: this.required,
                _error: this.error,
                _warn: this.warn,
                _disabled: this.disabled
            });

            return this;
        },

        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInitialValue: function () {
            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        /**
         * Sets 'value' as 'hidden' property's value, triggers 'toggle' event,
         * sets instance's hidden identifier in params storage based on
         * 'value'.
         *
         * @returns {Abstract} Chainable.
         */
        setVisible: function (isVisible) {
            this.visible(isVisible);

            return this;
        },

        /**
         * Show element.
         *
         * @returns {Abstract} Chainable.
         */
        show: function () {
            this.visible(true);

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {Abstract} Chainable.
         */
        hide: function () {
            this.visible(false);

            return this;
        },

        /**
         * Disable element.
         *
         * @returns {Abstract} Chainable.
         */
        disable: function () {
            this.disabled(true);

            return this;
        },

        /**
         * Enable element.
         *
         * @returns {Abstract} Chainable.
         */
        enable: function () {
            this.disabled(false);

            return this;
        },

        /**
         *
         * @param {(String|Object)} rule
         * @param {(Object|Boolean)} [options]
         * @returns {Abstract} Chainable.
         */
        setValidation: function (rule, options) {
            var rules = utils.copy(this.validation),
                changed;

            if (_.isObject(rule)) {
                _.extend(this.validation, rule);
            } else {
                this.validation[rule] = options;
            }

            changed = utils.compare(rules, this.validation).equal;

            if (changed) {
                this.required(!!rules['required-entry']);
                this.validate();
            }

            return this;
        },

        /**
         * Returns unwrapped preview observable.
         *
         * @returns {String} Value of the preview observable.
         */
        getPreview: function () {
            return this.value();
        },

        /**
         * Checks if element has addons
         *
         * @returns {Boolean}
         */
        hasAddons: function () {
            return this.addbefore || this.addafter;
        },

        /**
         * Checks if element has service setting
         *
         * @returns {Boolean}
         */
        hasService: function () {
            return this.service && this.service.template;
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var notEqual = this.value() !== this.initialValue;

            return !this.visible() ? false : notEqual;
        },

        /**
         * Checks if 'value' is not empty.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !utils.isEmpty(this.value());
        },

        /**
         * Sets value observable to initialValue property.
         *
         * @returns {Abstract} Chainable.
         */
        reset: function () {
            this.value(this.initialValue);
            this.error(false);

            return this;
        },

        /**
         * Sets current state as initial.
         */
        overload: function () {
            this.setInitialValue();
            this.bubble('update', this.hasChanged());
        },

        /**
         * Clears 'value' property.
         *
         * @returns {Abstract} Chainable.
         */
        clear: function () {
            this.value('');

            return this;
        },

        /**
         * Converts values like 'null' or 'undefined' to an empty string.
         *
         * @param {*} value - Value to be processed.
         * @returns {*}
         */
        normalizeData: function (value) {
            return utils.isEmpty(value) ? '' : value;
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Object} Validate information.
         */
        validate: function () {
            var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                message = !this.disabled() && this.visible() ? result.message : '',
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error(message);
            this.bubble('error', message);

            //TODO: Implement proper result propagation for form
            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this.bubble('update', this.hasChanged());

            this.validate();
        },

        /**
         * Restore value to default
         */
        restoreToDefault: function () {
            this.value(this.default);
            this.focused(true);
        },

        /**
         * Update whether value differs from default value
         */
        setDifferedFromDefault: function () {
            var value = typeof this.value() != 'undefined' && this.value() !== null ? this.value() : '',
                defaultValue = typeof this.default != 'undefined' && this.default !== null ? this.default : '';

            this.isDifferedFromDefault(value !== defaultValue);
        },

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function (state) {
            this.disabled(state);

            if (this.source && this.hasService()) {
                this.source.set('data.use_default.' + this.index, Number(state));
            }
        },

        /**
         *  Callback when value is changed by user
         */
        userChanges: function () {
            this.valueChangedByUser = true;
        },

        /**
         * Returns correct id for 'aria-describedby' accessibility attribute
         *
         * @returns {Boolean|String}
         */
        getDescriptionId: function () {
            var id = false;

            if (this.error()) {
                id = this.errorId;
            } else if (this.notice()) {
                id = this.noticeId;
            }

            return id;
        }
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'uiElement'
], function (ko, _, utils, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/paging/sizes',
            value: 20,
            minSize: 1,
            maxSize: 999,
            options: {
                '20': {
                    value: 20,
                    label: 20
                },
                '30': {
                    value: 30,
                    label: 30
                },
                '50': {
                    value: 50,
                    label: 50
                },
                '100': {
                    value: 100,
                    label: 100
                },
                '200': {
                    value: 200,
                    label: 200
                }
            },
            statefull: {
                options: true,
                value: true
            },
            listens: {
                value: 'onValueChange',
                options: 'onSizesChange'
            }
        },

        /**
         * Initializes sizes component.
         *
         * @returns {Sizes} Chainable.
         */
        initialize: function () {
            this._super()
                .updateArray();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Sizes} Chainable.
         */
        initObservable: function () {
            this._super()
                .track([
                    'value',
                    'editing',
                    'customVisible',
                    'customValue'
                ])
                .track({
                    optionsArray: []
                });

            this._value = ko.pureComputed({
                read: ko.getObservable(this, 'value'),

                /**
                 * Validates input field prior to updating 'value' property.
                 */
                write: function (value) {
                    value = this.normalize(value);

                    this.value = value;
                    this._value.notifySubscribers(value);
                },

                owner: this
            });

            return this;
        },

        /**
         * Starts editing of the specified size.
         *
         * @param {Number} value - Value of the size.
         * @returns {Sizes} Chainable.
         */
        edit: function (value) {
            this.editing = value;

            return this;
        },

        /**
         * Discards changes made to the currently editable size.
         *
         * @returns {Sizes} Chainable.
         */
        discardEditing: function () {
            var value = this.editing;

            if (value) {
                this.updateSize(value, value);
            }

            return this;
        },

        /**
         * Invokes 'discardEditing' and 'discardCustom' actions.
         *
         * @returns {Sizes} Chainable.
         */
        discardAll: function () {
            this.discardEditing()
                .discardCustom();

            return this;
        },

        /**
         * Returns value of the first size.
         *
         * @returns {Number}
         */
        getFirst: function () {
            return this.optionsArray[0].value;
        },

        /**
         * Returns size which matches specified value.
         *
         * @param {Number} value - Value of the item.
         * @returns {Object|Undefined}
         */
        getSize: function (value) {
            return this.options[value];
        },

        /**
         * Sets current size to the specified value.
         *
         * @param {Number} value - Value of the size.
         * @returns {Sizes} Chainable.
         */
        setSize: function (value) {
            this.value = value;

            return this;
        },

        /**
         * Adds a new value to sizes list.
         *
         * @param {Number} value - Value to be added.
         * @returns {Sizes} Chainable.
         */
        addSize: function (value) {
            var size;

            if (!this.hasSize(value)) {
                size = this.createSize(value);

                this.set('options.' + value, size);
            }

            return this;
        },

        /**
         * Removes provided value from the sizes list.
         *
         * @param {Number} value - Value to be removed.
         * @returns {Sizes} Chainable.
         */
        removeSize: function (value) {
            if (!this.hasSize(value)) {
                return this;
            }

            this.remove('options.' + value);

            if (this.isSelected(value)) {
                this.setSize(this.getFirst());
            }

            return this;
        },

        /**
         * Updates existing value to the provided one. If new value
         * is not specified, then sizes' '_value' property will be taken.
         *
         * @param {Number} value - Existing value that should be updated.
         * @param {(Number|String)} [newValue=size._value] - New size value.
         * @returns {Sizes} Chainable.
         */
        updateSize: function (value, newValue) {
            var size = this.getSize(value);

            if (!size) {
                return this;
            }

            newValue = newValue || size._value;

            if (isNaN(+newValue)) {
                this.discardEditing();

                return this;
            }

            newValue = this.normalize(newValue);

            this.remove('options.' + value)
                .addSize(newValue);

            if (this.isSelected(value)) {
                this.setSize(newValue);
            }

            return this;
        },

        /**
         * Creates new editable size instance with the provided value.
         *
         * @param {Number} value - Value of the size.
         * @returns {Object}
         */
        createSize: function (value) {
            return {
                value: value,
                label: value,
                _value: value,
                editable: true
            };
        },

        /**
         * Checks if provided value exists in the sizes list.
         *
         * @param {Number} value - Value to be checked.
         * @returns {Boolean}
         */
        hasSize: function (value) {
            return !!this.getSize(value);
        },

        /**
         * Hides and clears custom field.
         *
         * @returns {Sizes} Chainable.
         */
        discardCustom: function () {
            this.hideCustom()
                .clearCustom();

            return this;
        },

        /**
         * Shows custom field.
         *
         * @returns {Sizes} Chainable.
         */
        showCustom: function () {
            this.customVisible = true;

            return this;
        },

        /**
         * Hides custom field.
         *
         * @returns {Sizes} Chainable.
         */
        hideCustom: function () {
            this.customVisible = false;

            return this;
        },

        /**
         * Empties value of the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        clearCustom: function () {
            this.customValue = '';

            return this;
        },

        /**
         * Adds a new size specified in the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        applyCustom: function () {
            var value = this.customValue;

            value = this.normalize(value);

            this.addSize(value)
                .setSize(value)
                .discardCustom();

            return this;
        },

        /**
         * Checks if custom field is visible.
         *
         * @returns {Boolean}
         */
        isCustomVisible: function () {
            return this.customVisible;
        },

        /**
         * Converts provided value to a number and puts
         * it in range between 'minSize' and 'maxSize' properties.
         *
         * @param {(Number|String)} value - Value to be normalized.
         * @returns {Number}
         */
        normalize: function (value) {
            value = +value;

            if (isNaN(value)) {
                return this.getFirst();
            }

            return utils.inRange(Math.round(value), this.minSize, this.maxSize);
        },

        /**
         * Updates the array of options.
         *
         * @returns {Sizes} Chainable.
         */
        updateArray: function () {
            var array = _.values(this.options);

            this.optionsArray = _.sortBy(array, 'value');

            return this;
        },

        /**
         * Checks if provided value is in editing state.
         *
         * @param {Number} value - Value to be checked.
         * @returns {Boolean}
         */
        isEditing: function (value) {
            return this.editing === value;
        },

        /**
         * Checks if provided value is selected.
         *
         * @param {Number} value - Value to be checked.
         * @returns {Boolean}
         */
        isSelected: function (value) {
            return this.value === value;
        },

        /**
         * Listener of the 'value' property changes.
         */
        onValueChange: function () {
            this.discardAll()
                .trigger('close');
        },

        /**
         * Listener of the 'options' object changes.
         */
        onSizesChange: function () {
            this.editing = false;

            this.updateArray();
        }
    });
});

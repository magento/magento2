/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (ko, _, Collapsible) {
    'use strict';

    /**
     * Returns closest existing page number to page argument
     * @param {Number} value
     * @param {Number} max
     * @returns {Number} closest existing page number
     */
    function getInRange(value, min, max) {
        return Math.min(Math.max(min, value), max);
    }

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/paging/sizes',
            editing: false,
            value: 20,
            options: [],
            minSize: 1,
            maxSize: 1000,
            links: {
                size: '${ $.storageConfig.path }.value',
                options: '${ $.storageConfig.path }.options'
            },
            listens: {
                value: 'onValueChange',
                options: 'onSizesChange'
            }
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('options editing value');

            this.custom = {
                value: ko.observable(),
                visible: ko.observable(false)
            };

            return this;
        },

        /**
         * Starts editing of the specified size.
         *
         * @param {Number} value - Value of the size.
         * @returns {Sizes} Chainable.
         */
        edit: function (value) {
            this.editing(value);

            return this;
        },

        /**
         * Discards changes made to the currently editable size.
         *
         * @returns {Sizes} Chainable.
         */
        discard: function () {
            var value = this.editing();

            if (value) {
                this.updateSize(value, value);
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
         * Returns value of the first size.
         *
         * @returns {Number}
         */
        getFirst: function () {
            var size = this.options()[0];

            return size.value;
        },

        /**
         * Returns size which matches specified value.
         *
         * @param {Number} value - Value of the item in sizes array.
         * @returns {Object|Undefined}
         */
        getSize: function (value) {
            return _.findWhere(this.options(), {
                value: value
            });
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        setSize: function (value) {
            this.value(value);

            return this;
        },

        /**
         * Chechks if provided value exists in the sizes list.
         *
         * @returns {Boolean}
         */
        hasSize: function (value) {
            return !!this.getSize(value);
        },

        /**
         * Adds a new value to sizes list.
         *
         * @param {Number} value - Value to be added.
         * @returns {Sizes} Chainable.
         */
        addSize: function (value) {
            var options = this.options(),
                size;

            if (this.hasSize(value)) {
                return this;
            }

            size = this.createSize(value);

            options.push(size);

            options = this.sort(options);

            this.options(options);

            return this;
        },

        /**
         * Removes provided value from the sizes list.
         *
         * @param {Number} value - Value to be removed.
         * @returns {Sizes} Chainable.
         */
        removeSize: function (value, isUpdate) {
            var size = this.getSize(value);

            if (!size) {
                return this;
            }

            this.options.remove(size);

            if (!isUpdate && this.isSelected(value)) {
                this.setSize(this.getFirst());
            }

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        updateSize: function (value, newValue) {
            var size = this.getSize(value);

            if (!size) {
                return this;
            }

            newValue = newValue || size._value;
            newValue = this.normalize(newValue);

            this.removeSize(value, true)
                .addSize(newValue);

            if (this.isSelected(value)) {
                this.setSize(newValue);
            }

            return this;
        },

        /**
         * Hides and empties custom field.
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
            this.custom.visible(true);

            return this;
        },

        /**
         * Hides custom field.
         *
         * @returns {Sizes} Chainable.
         */
        hideCustom: function () {
            this.custom.visible(false);

            return this;
        },

        /**
         * Empties value of the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        clearCustom: function () {
            this.custom.value('');

            return this;
        },

        /**
         * Adds a new size specified in the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        applyCustom: function () {
            var value = this.custom.value();

            value = this.normalize(value);

            this.addSize(value)
                .setSize(value)
                .discardCustom();

            return this;
        },

        /**
         *
         * @param {(Number|String)} value
         * @returns {Number|Boolean}
         */
        normalize: function (value) {
            var result = +value;

            if (_.isString(value) && value.trim() !== +result + '') {
                result = this.getFirst();
            }

            return getInRange(result, this.minSize, this.maxSize);
        },

        /**
         * Checks if provided value is in editing state.
         *
         * @returns {Boolean}
         */
        isEditing: function (value) {
            return this.editing() === value;
        },

        /**
         *
         * @returns {Boolean}
         */
        isSelected: function (value) {
            return this.value() === value;
        },

        /**
         * Sorts provided array in ascending order by
         * the 'value' property of its' items.
         *
         * @param {Array} [data=this.options] - Array to be sorted.
         * @returns {Array} Sorted array.
         */
        sort: function (data) {
            data = data || this.options();

            return _.sortBy(data, 'value');
        },

        /**
         * Overrides original method to
         * discard all unapplied editings.
         *
         * @returns {Sizes} Chainable.
         */
        close: function () {
            this._super()
                .discardCustom()
                .discard();

            return this;
        },

        /**
         * Listener of the 'value' property changes.
         */
        onValueChange: function () {
            this.close();
        },

        /**
         * Listener of the 'options' array changes.
         */
        onSizesChange: function () {
            this.editing(false);
        }
    });
});

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
            size: 20,
            options: [],
            minSize: 1,
            maxSize: 1000,
            links: {
                size: '${ $.storageConfig.path }.size',
                options: '${ $.storageConfig.path }.options'
            },
            listens: {
                size: 'onSizeChange'
            }
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('options editing size');

            this.custom = {
                value: ko.observable(),
                visible: ko.observable(false)
            };

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        edit: function (size) {
            this.editing(size);

            return this;
        },

        /**
         *
         * @returns {Object}
         */
        createSize: function (size) {
            return {
                value: size,
                label: size,
                _value: size,
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
         * Returns size which matches specified value
         *
         * @param {Number} value
         * @returns {Object}
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
            this.size(value);

            return this;
        },

        /**
         *
         * @returns {Boolean}
         */
        hasSize: function (value) {
            return !!this.getSize(value);
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        addSize: function (value) {
            var options = this.options();

            if (this.hasSize(value)) {
                return this;
            }

            options.push(this.createSize(value));

            this.options(this.sort(options));

            return this;
        },

        /**
         *
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
        updateSize: function (value) {
            var newValue = this.getSize(value)._value;

            newValue = this.normalizeValue(newValue);

            if (value !== newValue) {
                this.removeSize(value, true)
                    .addSize(newValue);
            }

            this.editing(false);

            if (this.isSelected(value)) {
                this.setSize(newValue);
            }

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        showCustom: function () {
            this.custom.visible(true);

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        hideCustom: function () {
            this.custom.visible(false);

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        clearCustom: function () {
            this.custom.value('');

            return this;
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        applyCustom: function () {
            var value = this.custom.value();

            value = this.normalizeValue(value);

            this.addSize(value)
                .setSize(value)
                .hideCustom()
                .clearCustom();

            return this;
        },

        /**
         *
         * @param {(Number|String)} value
         * @returns {Number|Boolean}
         */
        normalizeValue: function (value) {
            var result;

            value = (value || '').trim();
            result = +value;

            if (value !== +result + '') {
                result = this.getFirst();
            }

            return getInRange(result, this.minSize, this.maxSize);
        },

        /**
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
            return this.size() === value;
        },

        /**
         *
         * @returns {Array}
         */
        sort: function (data) {
            data = data || this.options();

            return _.sortBy(data, 'value');
        },

        /**
         *
         */
        onSizeChange: function () {
            this.close();
        }
    });
});

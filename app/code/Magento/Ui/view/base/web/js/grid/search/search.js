/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'mageUtils',
    'uiElement'
], function (_, layout, $t, utils, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/search/search',
            placeholder: $t('Search by keyword'),
            label: $t('Keyword'),
            value: '',
            statefull: {
                value: true
            },
            imports: {
                inputValue: 'value',
                updatePreview: 'value'
            },
            exports: {
                value: '${ $.provider }:params.search'
            },
            modules: {
                chips: '${ $.chipsProvider }'
            }
        },

        /**
         * Initializes search component.
         *
         * @returns {Search} Chainable.
         */
        initialize: function () {
            this._super()
                .initChips();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Search} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('inputValue value')
                .track({
                    previews: []
                });

            return this;
        },

        /**
         * Initializes chips component.
         *
         * @returns {Search} Chainbale.
         */
        initChips: function () {
            this.chips('insertChild', this, 0);

            return this;
        },

        /**
         * Clears search.
         *
         * @returns {Search} Chainable.
         */
        clear: function () {
            this.value = '';

            return this;
        },

        /**
         * Resets input value to the last applied state.
         *
         * @returns {Search} Chainable.
         */
        cancel: function () {
            this.inputValue = this.value;

            return this;
        },

        /**
         * Applies search query.
         *
         * @param {String} [value=inputValue] - If not specfied, then
         *      value of the input field will be used.
         * @returns {Search} Chainable.
         */
        apply: function (value) {
            value = value || this.inputValue;

            this.value = this.inputValue = value;

            return this;
        },

        /**
         * Updates preview data.
         *
         * @returns {Search} Chainable.
         */
        updatePreview: function () {
            var preview = [];

            if (this.value) {
                preview.push({
                    elem: this,
                    label: this.label,
                    preview: this.value
                });
            }

            this.previews = preview;

            return this;
        }
    });
});

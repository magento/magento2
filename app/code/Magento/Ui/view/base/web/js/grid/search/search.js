/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'mageUtils',
    'uiElement',
    'jquery'
], function (_, layout, $t, utils, Element, $) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/search/search',
            placeholder: 'Search by keyword',
            label: $t('Keyword'),
            value: '',
            previews: [],
            chipsProvider: 'componentType = filtersChips, ns = ${ $.ns }',
            statefull: {
                value: true
            },
            tracks: {
                value: true,
                previews: true,
                inputValue: true,
                focused: true
            },
            imports: {
                inputValue: 'value',
                updatePreview: 'value',
                focused: false
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
            var urlParams = window.location.href.slice(window.location.href.search('[\&\?](search=)')).split('&'),
                searchTerm = [];

            this._super()
                .initChips();

            if (urlParams[0]) {
                searchTerm = urlParams[0].split('=');

                if (searchTerm[1]) {
                    this.apply(decodeURIComponent(searchTerm[1]));
                }
            }

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
         * Click To ScrollTop.
         */
        scrollTo: function ($data) {
            $('html, body').animate({
                scrollTop: 0
            }, 'slow', function () {
                $data.focused = false;
                $data.focused = true;
            });
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
         * @param {String} [value=inputValue] - If not specified, then
         *      value of the input field will be used.
         * @returns {Search} Chainable.
         */
        apply: function (value) {
            value = value || this.inputValue;

            this.value = this.inputValue = value.trim();

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

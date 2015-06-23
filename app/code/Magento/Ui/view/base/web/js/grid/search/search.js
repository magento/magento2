/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiLayout',
    'mage/translate',
    'mageUtils',
    'uiComponent'
], function (_, layout, $t, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/search/search',
            placeholder: $t('Search by keyword'),
            label: $t('Keyword'),
            optionsConfig: {
                provider: '${ $.optionsConfig.name }',
                name: '${ $.name }_options',
                component: 'Magento_Ui/js/grid/search/options'
            },
            imports: {
                inputValue: 'value',
                updatePreview: 'value'
            },
            exports: {
                value: '${ $.provider }:params.search'
            },
            links: {
                value: '${ $.storageConfig.provider }:${ $.storageConfig.namespace }'
            },
            modules: {
                options: '${ $.optionsConfig.provider }',
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
                .initOptions()
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
                .observe([
                    'inputValue',
                    'value'
                ])
                .observe({
                    previews: []
                });

            return this;
        },

        /**
         * Initializes options component.
         *
         * @returns {Search} Chainable.
         */
        initOptions: function () {
            layout([this.optionsConfig]);

            this.options('setTarget', {
                model: this,
                value: 'inputValue',
                navigate: 'inputValue',
                select: 'apply'
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
            this.value('');

            return this;
        },

        /**
         * Resets input value to the last applied state.
         *
         * @returns {Search} Chainable.
         */
        cancel: function () {
            this.inputValue(this.value());

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
            value = value || this.inputValue();

            this.options('clear');

            this.value(value);
            this.inputValue(value);

            return this;
        },

        /**
         * Updates preview data.
         *
         * @returns {Search} Chainable.
         */
        updatePreview: function () {
            var preview = [];

            if (this.value()) {
                preview.push({
                    elem: this,
                    label: this.label,
                    preview: this.value()
                });
            }

            this.previews(preview);

            return this;
        },

        /**
         * Inputs' field 'keyup' event handler.
         *
         * @param {Oject} model - Model associated with an input field.
         * @param {KeyboardEvent} e - Event object.
         */
        onKeyUp: function (model, e) {
            var code = e.keyCode;

            switch (code) {
                case 13:
                    this.apply();
                    break;

                case 27:
                    this.cancel();
                    break;

                default:
                    this.options('applyKey', code);
            }
        }
    });
});

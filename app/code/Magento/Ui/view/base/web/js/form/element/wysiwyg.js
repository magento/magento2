/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    './abstract',
    'mage/adminhtml/events',
    'Magento_Variable/variables'
], function ($, _, ko, Abstract, varienGlobalEvents) {
    'use strict';

    return Abstract.extend({
        wysiwyg: undefined,
        defaults: {
            elementSelector: 'textarea',
            suffixRegExpPattern: '${ $.wysiwygUniqueSuffix }',
            $wysiwygEditorButton: '',
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },
            template: 'ui/form/field',
            elementTmpl: 'ui/form/element/wysiwyg',
            content:        '',
            showSpinner:    false,
            loading:        false,
            listens: {
                disabled: 'setDisabled'
            }
        },

        /**
         *
         * @returns {} Chainable.
         */
        initialize: function () {
            this._super()
                .initNodeListener();

            $.async({
                component: this,
                selector: 'button'
            }, function (element) {
                this.$wysiwygEditorButton = this.$wysiwygEditorButton ?
                    this.$wysiwygEditorButton.add($(element)) : $(element);
            }.bind(this));

            // disable editor completely after initialization is field is disabled
            varienGlobalEvents.attachEventHandler('wysiwygEditorInitialized', function () {
                if (typeof window.tinyMceEditors !== 'undefined') {
                    this.wysiwyg = window.tinyMceEditors[this.wysiwygId];
                }

                if (this.disabled()) {
                    this.setDisabled(true);
                }
            }.bind(this));

            return this;
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var pattern = config.suffixRegExpPattern || this.constructor.defaults.suffixRegExpPattern;

            pattern = pattern.replace(/\$/g, '\\$&');
            config.content = config.content.replace(new RegExp(pattern, 'g'), this.getUniqueSuffix(config));
            this._super();

            return this;
        },

        /**
         * Build unique id based on name, underscore separated.
         *
         * @param {Object} config
         */
        getUniqueSuffix: function (config) {
            return config.name.replace(/(\.|-)/g, '_');
        },

        /**
         * @inheritdoc
         */
        destroy: function () {
            this._super();

            if (typeof this.wysiwyg !== 'undefined' && this.wysiwyg) {
                this.wysiwyg.removeEvents(this.wysiwygId);
            }
        },

        /**
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super()
                .observe('value');

            return this;
        },

        /**
         *
         * @returns {} Chainable.
         */
        initNodeListener: function () {
            $.async({
                component: this,
                selector: this.elementSelector
            }, this.setElementNode.bind(this));

            return this;
        },

        /**
         *
         * @param {HTMLElement} node
         */
        setElementNode: function (node) {
            $(node).bindings({
                value: this.value
            });
        },

        /**
         * Set disabled property to wysiwyg component
         *
         * @param {Boolean} disabled
         */
        setDisabled: function (disabled) {
            if (this.$wysiwygEditorButton && disabled) {
                this.$wysiwygEditorButton.prop('disabled', 'disabled');
            } else if (this.$wysiwygEditorButton) {
                this.$wysiwygEditorButton.removeProp('disabled');
            }

            /* eslint-disable no-undef */

            if (typeof this.wysiwyg !== 'undefined' && this.wysiwyg && this.wysiwyg.activeEditor()) {
                if (disabled) {
                    this.wysiwyg.setEnabledStatus(false);
                    this.wysiwyg.getPluginButtons().prop('disabled', 'disabled');
                } else {
                    this.wysiwyg.setEnabledStatus(true);
                    this.wysiwyg.getPluginButtons().removeProp('disabled');
                }
            }
        }
    });
});

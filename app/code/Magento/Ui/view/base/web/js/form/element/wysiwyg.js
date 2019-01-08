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
    'Magento_Variable/variables'
], function ($, _, ko, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementSelector: 'textarea',
            suffixRegExpPattern: '\\${ \\$.wysiwygUniqueSuffix }',
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

            return this;
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var pattern = config.suffixRegExpPattern || this.constructor.defaults.suffixRegExpPattern;

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
         * @param {Boolean} status
         */
        setDisabled: function (status) {
            this.$wysiwygEditorButton.attr('disabled', status);

            /* eslint-disable no-undef */
            if (tinyMCE && tinyMCE.activeEditor) {
                _.each(tinyMCE.activeEditor.controlManager.controls, function (property, index, controls) {
                    controls[property.id].setDisabled(status);
                });

                tinyMCE.activeEditor.getBody().setAttribute('contenteditable', !status);
            }

            /* eslint-enable  no-undef*/
        }
    });
});

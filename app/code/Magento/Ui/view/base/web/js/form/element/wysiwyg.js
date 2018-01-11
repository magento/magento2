/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'wysiwygAdapter',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    './abstract',
    'Magento_Variable/variables'
], function (wysiwyg, $, _, ko, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementSelector: 'textarea',
            value: '',
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
                this.$wysiwygEditorButton = $(element);
            }.bind(this));

            return this;
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
            if (this.$wysiwygEditorButton) {
                this.$wysiwygEditorButton.attr('disabled', disabled);
            }

            if (!_.isUndefined(wysiwyg)) {
                if (disabled) {
                    wysiwyg.setToolbarStatus(false);
                    wysiwyg.getPluginButtons().attr('disabled', 'disabled');
                    wysiwyg.getTextArea().attr('disabled', 'disabled');
                } else {
                    wysiwyg.setToolbarStatus(true);
                    wysiwyg.getPluginButtons().removeAttr('disabled');
                    wysiwyg.getTextArea().removeAttr('disabled');
                }
            }
            /* eslint-enable  no-undef*/
        }
    });
});
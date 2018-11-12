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
    'mage/adminhtml/events',
    'Magento_Variable/variables'
], function (wysiwyg, $, _, ko, Abstract, varienGlobalEvents) {
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
                this.$wysiwygEditorButton = this.$wysiwygEditorButton ?
                    this.$wysiwygEditorButton.add($(element)) : $(element);
            }.bind(this));

            // disable editor completely after initialization is field is disabled
            varienGlobalEvents.attachEventHandler('wysiwygEditorInitialized', function () {
                if (this.disabled()) {
                    this.setDisabled(true);
                }
            }.bind(this));

            return this;
        },

        /**
         * @inheritdoc
         */
        destroy: function () {
            this._super();
            wysiwyg.removeEvents(this.wysiwygId);
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
            if (typeof wysiwyg !== 'undefined' && wysiwyg.activeEditor()) {
                if (wysiwyg && disabled) {
                    wysiwyg.setEnabledStatus(false);
                    wysiwyg.getPluginButtons().prop('disabled', 'disabled');
                } else if (wysiwyg) {
                    wysiwyg.setEnabledStatus(true);
                    wysiwyg.getPluginButtons().removeProp('disabled');
                }
            }
        }
    });
});

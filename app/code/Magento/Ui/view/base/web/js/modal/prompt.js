/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _) {
    'use strict';

    $.widget('mage.prompt', $.mage.modal, {
        options: {
            modalClass: 'prompt',
            promptField: '[data-role="promptField"]',
            value: '',
            actions: {

                /**
                 * Callback always - called on all actions.
                 */
                always: function () {},

                /**
                 * Callback confirm.
                 */
                confirm: function () {},

                /**
                 * Callback cancel.
                 */
                cancel: function () {}
            },
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'action-secondary action-dismiss',

                /**
                 * Click handler.
                 */
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('OK'),
                class: 'action-primary action-accept',

                /**
                 * Click handler.
                 */
                click: function () {
                    this.closeModal(true);
                }
            }]
        },

        /**
         * Create widget.
         */
        _create: function () {
            this.options.focus = this.options.promptField;
            this._super();
            this.modal.find(this.options.modalContent).append(
                '<div class="prompt-message">' +
                    '<input data-role="promptField" id="prompt-field" class="admin__control-text" type="text"/>' +
                '</div>'
            );
            this.modal.find(this.options.modalCloseBtn).off().on('click',  _.bind(this.closeModal, this, false));
            this.openModal();
        },

        /**
         * Compile template and append to wrapper.
         */
        _renderModal: function () {
            this._super();
            this.element.wrap('<label for="prompt-field"></label>');
        },

        /**
         * Remove widget
         */
        _remove: function () {
            this.modal.remove();
        },

        /**
         * Open modal window
         */
        openModal: function () {
            this._super();
            this.modal.find(this.options.promptField).val(this.options.value);
        },

        /**
         * Close modal window
         */
        closeModal: function (result) {
            var value;

            if (result) {
                value = this.modal.find(this.options.promptField).val();
                this.options.actions.confirm(value);
            } else {
                this.options.actions.cancel();
            }
            this.options.actions.always();
            this.element.bind('promptclosed', _.bind(this._remove, this));

            return this._super();
        }
    });

    return function (config) {
        return $('<div></div>').html(config.content).prompt(config);
    };
});

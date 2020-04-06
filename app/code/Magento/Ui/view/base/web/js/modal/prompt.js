/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'text!ui/template/modal/modal-prompt-content.html',
    'jquery-ui-modules/widget',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, template, promptContentTmpl) {
    'use strict';

    $.widget('mage.prompt', $.mage.modal, {
        options: {
            modalClass: 'prompt',
            promptContentTmpl: promptContentTmpl,
            promptField: '[data-role="promptField"]',
            attributesForm: {},
            attributesField: {},
            value: '',
            validation: false,
            validationRules: [],
            keyEventHandlers: {

                /**
                 * Enter key press handler,
                 * submit result and close modal window
                 * @param {Object} event - event
                 */
                enterKey: function (event) {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal(true);
                        event.preventDefault();
                    }
                },

                /**
                 * Tab key press handler,
                 * set focus to elements
                 */
                tabKey: function () {
                    if (document.activeElement === this.modal[0]) {
                        this._setFocus('start');
                    }
                },

                /**
                 * Escape key press handler,
                 * cancel and close modal window
                 * @param {Object} event - event
                 */
                escapeKey: function (event) {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal();
                        event.preventDefault();
                    }
                }
            },
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
            this.options.validation = this.options.validation && this.options.validationRules.length;
            this.options.outerClickHandler = this.options.outerClickHandler || _.bind(this.closeModal, this, false);
            this._super();
            this.modal.find(this.options.modalContent).append(this.getFormTemplate());
            this.modal.find(this.options.modalCloseBtn).off().on('click',  _.bind(this.closeModal, this, false));

            if (this.options.validation) {
                this.setValidationClasses();
            }

            this.openModal();
        },

        /**
         * Form template getter.
         *
         * @returns {Object} Form template.
         */
        getFormTemplate: function () {
            var formTemplate,
                formAttr = '',
                inputAttr = '',
                attributeName;

            for (attributeName in this.options.attributesForm) {
                if (this.options.attributesForm.hasOwnProperty(attributeName)) {
                    formAttr = formAttr + ' ' + attributeName + '="' +
                        this.options.attributesForm[attributeName] + '"';
                }
            }

            for (attributeName in this.options.attributesField) {
                if (this.options.attributesField.hasOwnProperty(attributeName)) {
                    inputAttr = inputAttr + ' ' + attributeName + '="' +
                        this.options.attributesField[attributeName] + '"';
                }
            }

            formTemplate = $(template(this.options.promptContentTmpl, {
                data: this.options,
                formAttr: formAttr,
                inputAttr: inputAttr
            }));

            return formTemplate;
        },

        /**
         * Remove widget
         */
        _remove: function () {
            this.modal.remove();
        },

        /**
         * Validate prompt field
         */
        validate: function () {
            return $.validator.validateSingleElement(this.options.promptField);
        },

        /**
         * Add validation classes to prompt field
         */
        setValidationClasses: function () {
            this.modal.find(this.options.promptField).attr('class', $.proxy(function (i, val) {
                return val + ' ' + this.options.validationRules.join(' ');
            }, this));
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
                if (this.options.validation && !this.validate()) {
                    return false;
                }

                value = this.modal.find(this.options.promptField).val();
                this.options.actions.confirm.call(this, value);
            } else {
                this.options.actions.cancel.call(this, result);
            }

            this.options.actions.always();
            this.element.bind('promptclosed', _.bind(this._remove, this));

            return this._super();
        }
    });

    return function (config) {
        return $('<div class="prompt-message"></div>').html(config.content).prompt(config);
    };
});

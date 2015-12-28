/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiCollection',
    'underscore',
    './modal'
], function ($, Collection, _) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'ui/modal/modal-component',
            options: {
                title: '',
                buttons: []
            },
            valid: true
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            _.bindAll(this,
                'initModal',
                'openModal',
                'closeModal',
                'toggleModal',
                'setPrevValues',
                'actionCancel',
                'validate');
            this.initializeContent();

            return this;
        },

        /**
         * Initializes modal configuration
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.uniqueClass = this.name.replace(/\./g, '_');

            return this;
        },

        /**
         * Initialize modal's content components
         */
        initializeContent: function () {
            $.async('.' + this.uniqueClass(), this, this.initModal);
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Massactions} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('uniqueClass');

            return this;
        },

        /**
         * Wrap content in a modal of certain type
         *
         * @param {HTMLElement} element
         * @returns {Object} Chainable.
         */
        initModal: function (element) {
            if (!this.modal) {
                this.overrideModalButtonCallback();
                this.options.modalCloseBtnHandler = this.actionCancel;
                this.modal = $(element).modal(this.options);

                if (this.waitCbk) {
                    this.waitCbk();
                    this.waitCbk = null;
                }
            }

            return this;
        },

        /**
         * Open modal
         */
        openModal: function () {
            if (this.modal) {
                this.modal.modal('openModal');
                this.applyData();
            } else {
                this.waitCbk = this.openModal;
            }
        },

        /**
         * Close modal
         */
        closeModal: function () {
            if (this.modal) {
                this.modal.modal('closeModal');
            } else {
                this.waitCbk = this.closeModal;
            }
        },

        /**
         * Toggle modal
         */
        toggleModal: function () {
            var state;

            if (this.modal) {
                state = this.modal.data('mage-modal').options.isOpen;

                if (state) {
                    this.closeModal();
                } else {
                    this.openModal();
                }
            } else {
                this.waitCbk = this.toggleModal;
            }
        },

        /**
         * Validate everything validatable in modal
         */
        validate: function (elem) {
            if (typeof elem.validate === 'function') {
                this.valid = this.valid & elem.validate().valid;
            } else if (elem.elems) {
                elem.elems().forEach(this.validate, this);
            }
        },

        /**
         * Reset data from provider
         */
        resetData: function () {
            this.elems().forEach(this.resetValue, this);
        },

        /**
         * Update 'applied' property with data from modal content
         */
        applyData: function () {
            var applied = {};

            this.elems().forEach(this.gatherValues.bind(this, applied), this);
            this.applied = applied;
        },

        /**
         * Gather values from modal content
         *
         * @param {Array} applied
         * @param {HTMLElement} elem
         */
        gatherValues: function (applied, elem) {
            if (typeof elem.value === 'function') {
                applied[elem.index] = elem.value();
            } else if (elem.elems) {
                elem.elems().forEach(this.gatherValues.bind(this, applied), this);
            }
        },

        /**
         * Set to previous values from modal content
         *
         * @param {HTMLElement} elem
         */
        setPrevValues: function (elem) {
            if (typeof elem.value === 'function') {
                elem.value(this.applied[elem.index]);
            } else if (elem.elems) {
                elem.elems().forEach(this.setPrevValues, this);
            }
        },

        /**
         * Triggers some method in every modal child elem, if this method is defined
         *
         * @param {String} actionName
         * @param {String} targetName
         * @param {HTMLElement} elem
         */
        triggerActionInChildElem: function (actionName, targetName, elem) {
            if (typeof elem[actionName] === 'function' && (!targetName || elem.index === targetName)) {
                elem[actionName]();
            } else if (elem.elems) {
                elem.elems().forEach(this.triggerActionInChildElem.bind(this, actionName, targetName), this);
            }
        },

        /**
         * Override modal buttons callback placeholders with real callbacks
         */
        overrideModalButtonCallback: function () {
            var buttons = this.options.buttons;

            if (buttons && buttons.length) {
                buttons.forEach(function (button) {
                    button.click = this.getButtonClickHandler(button.click);
                }, this);
            }
        },

        /**
         * Override modal buttons callback placeholders with real callbacks
         */
        getButtonClickHandler: function (clickConfig) {
            if (_.isObject(clickConfig)) {
                return clickConfig.closeAfter ?
                    function () {
                        this.triggerActionInChildElem(clickConfig.actionName, clickConfig.targetName, this);
                        this.closeModal();
                    }.bind(this) :
                    this.triggerActionInChildElem.bind(this, clickConfig.actionName, clickConfig.targetName, this);
            }

            return (this[clickConfig] || this.actionNothing).bind(this);
        },

        /**
         * Placeholder for unrecognized action
         */
        actionNothing: function () {
        },

        /**
         * Cancels changes in modal:
         * returning elems values to the previous state,
         * and close modal
         */
        actionCancel: function () {
            this.elems().forEach(this.setPrevValues, this);
            this.closeModal();
        },

        /**
         * Accept changes in modal by not preventing them.
         * Can be extended by exporting 'gatherValues' result somewhere
         */
        actionDone: function () {
            this.valid = true;
            this.elems().forEach(this.validate, this);

            if (this.valid) {
                this.closeModal();
            }
        }
    });
});

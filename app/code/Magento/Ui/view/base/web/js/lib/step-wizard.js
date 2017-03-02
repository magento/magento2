/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'uiComponent',
    'jquery',
    'underscore',
    'ko',
    'mage/backend/notification',
    'mage/translate'
], function (uiRegistry, Component, $, _, ko) {
    'use strict';

    var Wizard;

    ko.utils.domNodeDisposal.cleanExternalData = _.wrap(
        ko.utils.domNodeDisposal.cleanExternalData,
        function (func, node) {
            if (!$(node).closest('[data-type=skipKO]').length) {
                func(node);
            }
        }
    );

    /**
     * Wizard constructor.
     *
     * @param {Array} steps
     * @param {String} modalClass
     * @constructor
     */
    Wizard = function (steps, modalClass) {
        this.steps = steps;
        this.index = 0;
        this.data = {};
        this.nextLabelText = $.mage.__('Next');
        this.prevLabelText = $.mage.__('Back');
        this.elementSelector = '[data-role=steps-wizard-main]';
        this.element = modalClass ? $('.' + modalClass + this.elementSelector) : $(this.elementSelector);
        this.nextLabel = '[data-role="step-wizard-next"]';
        this.prevLabel = '[data-role="step-wizard-prev"]';
        this.element.notification();

        /**
         * Move to newIndex.
         *
         * @param {Number} newIndex
         * @return {String}
         */
        this.move = function (newIndex) {
            if (!this.preventSwitch(newIndex)) {
                if (newIndex > this.index) {
                    this._next(newIndex);
                } else if (newIndex < this.index) {
                    this._prev(newIndex);
                }
            }
            this.updateLabels(this.getStep());
            this.showNotificationMessage();

            return this.getStep().name;
        };

        /**
         * Move wizard to next step.
         *
         * @return {String}
         */
        this.next = function () {
            this.move(this.index + 1);

            return this.getStep().name;
        };

        /**
         * Move wizard to previous step.
         *
         * @return {String}
         */
        this.prev = function () {
            this.move(this.index - 1);

            return this.getStep().name;
        };

        /**
         * @return {*}
         */
        this.preventSwitch = function (newIndex) {
            return newIndex < 0 || (newIndex - this.index) > 1;//eslint-disable-line no-extra-parens
        };

        /**
         * @param {Number} newIndex
         * @return {Boolean}
         * @private
         */
        this._next = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index + 1;

            try {
                this.getStep().force(this);

                if (newIndex >= steps.length) {
                    return false;
                }
            } catch (e) {
                this.setNotificationMessage(e.message, true);

                return false;
            }
            this.cleanErrorNotificationMessage();
            this.index = newIndex;
            this.cleanNotificationMessage();
            this.render();
        };

        /**
         * @param {Number} newIndex
         * @private
         */
        this._prev = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index - 1;
            this.getStep().back(this);
            this.index = newIndex;
        };

        /**
         * @param {Number} stepIndex
         * @return {Object}
         */
        this.getStep = function (stepIndex) {
            return this.steps[stepIndex || this.index] || {};
        };

        /**
         * @param {String} message
         * @param {String} error
         */
        this.notifyMessage = function (message, error) {
            $(this.element).notification('clear').notification('add', {
                error: error,
                message: message
            });
        };

        /**
         * @param {Object} step
         */
        this.updateLabels = function (step) {
            this.element.find(this.nextLabel).find('button').text(step.nextLabelText || this.nextLabelText);
            this.element.find(this.prevLabel).find('button').text(step.prevLabelText || this.prevLabelText);
        };

        /**
         * Show notification message.
         */
        this.showNotificationMessage = function () {
            if (!_.isEmpty(this.getStep())) {
                this.hideNotificationMessage();

                if (this.getStep().notificationMessage.text !== null) {
                    this.notifyMessage(
                        this.getStep().notificationMessage.text,
                        this.getStep().notificationMessage.error
                    );
                }
            }
        };

        /**
         * Remove notification message.
         */
        this.cleanNotificationMessage = function () {
            this.getStep().notificationMessage.text = null;
            this.hideNotificationMessage();
        };

        /**
         * Remove error message.
         */
        this.cleanErrorNotificationMessage = function () {
            if (this.getStep().notificationMessage.error === true) {
                this.cleanNotificationMessage();
            }
        };

        /**
         * @param {String} text
         * @param {String} error
         */
        this.setNotificationMessage = function (text, error) {
            error = error !== undefined;

            if (!_.isEmpty(this.getStep())) {
                this.getStep().notificationMessage.text = text;
                this.getStep().notificationMessage.error = error;
                this.showNotificationMessage();
            }
        };

        /**
         * Hide notification message.
         */
        this.hideNotificationMessage = function () {
            $(this.element).notification('clear');
        };

        /**
         * Render step.
         */
        this.render = function () {
            this.hideNotificationMessage();
            this.getStep().render(this);
        };

        /**
         * Initialize step.
         */
        this.init = function () {
            this.updateLabels(this.getStep());
            this.render();
        };
        this.init();
    };

    return Component.extend({
        defaults: {
            modalClass: '',
            initData: [],
            stepsNames: [],
            selectedStep: '',
            steps: [],
            disabled: true
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selectedStep.subscribe(this.wrapDisabledBackButton.bind(this));
        },

        /** @inheritdoc */
        initElement: function (step) {
            step.initData = this.initData;
            step.mode = _.all(this.initData, _.isEmpty) ? 'create' : 'edit';
            this.steps[this.getStepIndexByName(step.name)] = step;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe([
                'selectedStep',
                'disabled'
            ]);

            return this;
        },

        /** @inheritdoc */
        destroy: function () {
            _.each(this.steps, function (step) {
                step.destroy();
            });

            this._super();
        },

        /**
         * Toggle disable property.
         *
         * @param {String} stepName
         */
        wrapDisabledBackButton: function (stepName) {
            if (_.first(this.stepsNames) === stepName) {
                this.disabled(true);
            } else {
                this.disabled(false);
            }
        },

        /**
         * Get step by index name.
         *
         * @param {String} stepName
         */
        getStepIndexByName: function (stepName) {
            return _.indexOf(this.stepsNames, stepName);
        },
        //controls, todo to another object
        /**
         * Select next step.
         */
        next: function () {
            this.selectedStep(this.wizard.next());
        },

        /**
         * Select previous step.
         */
        back: function () {
            this.selectedStep(this.wizard.prev());
        },

        /**
         * Open wizard.
         */
        open: function () {
            this.selectedStep(this.stepsNames.first());
            this.wizard = new Wizard(this.steps, this.modalClass);
        },

        /**
         * Close wizard.
         */
        close: function () {
            var modal =  uiRegistry.get(this.initData.configurableModal);

            if (!_.isUndefined(modal)) {
                modal.closeModal();
            }
        },

        /**
         * @param {Object} data
         * @param {Object} event
         */
        showSpecificStep: function (data, event) {
            var index = _.indexOf(this.stepsNames, event.target.hash.substr(1)),
                stepName = this.wizard.move(index);

            this.selectedStep(stepName);
        }
    });
});

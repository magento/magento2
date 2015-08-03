/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiRegistry",
    "uiComponent",
    "jquery",
    "underscore",
    "ko",
    "mage/backend/notification"
], function (uiRegistry, Component, $, _, ko) {
    "use strict";

    ko.utils.domNodeDisposal.cleanExternalData = _.wrap(ko.utils.domNodeDisposal.cleanExternalData, function(func, node) {
        if (!$(node).closest('[data-type=skipKO]').length) {
            func(node);
        }
    });

    var Wizard = function (steps) {
        this.steps = steps;
        this.index = 0;
        this.data = {};
        this.element = $('[data-role=steps-wizard-main]');
        this.nextLabel = '[data-role="step-wizard-next"]';
        this.prevLabel = '[data-role="step-wizard-prev"]';
        this.nextLabelText = 'Next';
        this.prevLabelText = 'Back';
        $(this.element).notification();
        this.move = function (newIndex) {
            if (!this.preventSwitch(newIndex)) {
                if (newIndex > this.index) {
                    this._next(newIndex);
                } else if (newIndex < this.index) {
                    this._prev(newIndex);
                }
            }
            this.updateLabels(this.getStep());
            return this.getStep().name;
        };
        this.next = function () {
            this.move(this.index + 1);
            return this.getStep().name;
        };
        this.prev = function () {
            this.move(this.index - 1);
            return this.getStep().name;
        };
        this.preventSwitch = function(newIndex) {
            return newIndex < 0 || (newIndex - this.index) > 1;
        };
        this._next = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index + 1;
            try {
                this.getStep().force(this);
            } catch (e) {
                this.notifyMessage(e.message, true);
                return false;
            }
            this.index = newIndex;
            this.render();
        };
        this._prev = function (newIndex) {
            newIndex = _.isNumber(newIndex) ? newIndex : this.index - 1;
            this.getStep().back(this);
            this.index = newIndex;
        };
        this.getStep = function(stepIndex) {
            return this.steps[stepIndex || this.index] || {};
        };
        this.notifyMessage = function (message, error) {
            $(this.element).notification('clear').notification('add', {
                error: error,
                message: $.mage.__(message)
            });
        };
        this.updateLabels = function(step) {
            this.element.find(this.nextLabel).find('button').text(step.nextLabelText || this.nextLabelText);
            this.element.find(this.prevLabel).find('button').text(step.prevLabelText || this.prevLabelText);
        };
        this.render = function() {
            $(this.element).notification('clear');
            if (!_.isEmpty(this.getStep())) {
                this.getStep().render(this);
            }
        };
        this.render();
    };

    return Component.extend({
        defaults: {
            initData: [],
            stepsNames: [],
            selectedStep: '',
            steps: [],
            disabled: true
        },
        initialize: function () {
            this._super();
            this.selectedStep.subscribe(this.wrapDisabledBeckButton.bind(this));
        },
        initElement: function (step) {
            step.initData = this.initData;
            this.steps[this.getStepIndexByName(step.name)] = step;
        },
        initObservable: function () {
            this._super().observe([
                'selectedStep',
                'disabled'
            ]);
            return this;
        },
        wrapDisabledBeckButton: function(stepName) {
            if (_.first(this.stepsNames) === stepName) {
                this.disabled(true);
            } else {
                this.disabled(false);
            }
        },
        getStepIndexByName: function (stepName) {
            return _.indexOf(this.stepsNames, stepName);
        },
        //controls, todo to another object
        next: function () {
            this.selectedStep(this.wizard.next());
        },
        back: function () {
            this.selectedStep(this.wizard.prev());
        },
        open: function () {
            var $form = $('[data-form=edit-product]');
            if (!$form.valid()) {
                $form.data('validator').focusInvalid();
            } else {
                this.selectedStep(this.stepsNames.first());
                this.wizard = new Wizard(this.steps);
                $('[data-role=step-wizard-dialog]').trigger('openModal');
            }
        },
        close: function () {
            $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },
        showSpecificStep: function () {
            var index = _.indexOf(this.stepsNames, event.target.hash.substr(1));
            var stepName = this.wizard.move(index);
            this.selectedStep(stepName);
        }
    });
});

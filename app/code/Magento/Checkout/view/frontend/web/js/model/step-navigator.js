/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery', 'ko', 'Magento_Customer/js/model/customer'], function($, ko, customer) {
    var customerIsLoggedIn = customer.isLoggedIn()();
    return {
        currentStep: null,
        steps: [
            {
                name: 'authentication',
                isVisible: ko.observable(!customerIsLoggedIn),
                isEnabled: true,
                number: ko.observable(1)
            },
            {
                name: 'billingAddress',
                isVisible: ko.observable(customerIsLoggedIn),
                isEnabled: true,
                number: ko.observable(2)
            },
            {
                name: 'shippingAddress',
                isVisible: ko.observable(false),
                isEnabled: true,
                number: ko.observable(3)
            },
            {
                name: 'shippingMethod',
                isVisible: ko.observable(false),
                isEnabled: true,
                number: ko.observable(4)
            },
            {
                name: 'paymentMethod',
                isVisible: ko.observable(false),
                isEnabled: true,
                number: ko.observable(5)
            },
            {
                name: 'review',
                isVisible: ko.observable(false),
                isEnabled: true,
                number: ko.observable(6)
            }
        ],
        setCurrent: function(step) {
            this.currentStep = step;
            return this;
        },
        getCurrentStep: function() {
            if (!this.currentStep) {
                alert('Current step not set.');
                return;
            }
            var self = this;
            var currentStep = null;
            $.each(this.steps, function(key, step) {
                if (self.currentStep == step.name) {
                    currentStep = step;
                }
            });
            return currentStep;
        },
        goNext: function() {
            var currentStep = this.getCurrentStep();
            var nextStepOrder = currentStep.number() + 1;
            var nextStep = null;
            $.each(this.steps, function(key, item) {
                if (nextStepOrder == item.number()) {
                    nextStep = item;
                    return false;
                }
            });
            if (nextStep) {
                this.toStep(nextStep.name);
            }
        },
        goBack: function() {
            var currentStep = this.getCurrentStep();
            var prevStepOrder = currentStep.number() - 1;
            var previousStep = null;
            $.each(this.steps, function(key, item) {
                if (prevStepOrder == item.number()) {
                    previousStep = item;
                    return false;
                }
            });
            if (previousStep) {
                this.toStep(previousStep.name);
            }
        },
        goToStep: function(name) {
            var visibleStep = this.getCurrentVisibleStep();
            var step = this.findStepByName(name);
            if (step.number() < visibleStep.number()) {
                this.toStep(name);
            }
        },
        toStep: function(name) {
            if (name) {
                $.each(this.steps, function(key, step) {
                    step.isVisible(false);
                });
                this.findStepByName(name).isVisible(true);
            }
        },
        findStepByName: function(name) {
            var step = null;
            $.each(this.steps, function(key, currentStep) {
                if (name == currentStep.name) {
                    step = currentStep;
                    return false;
                }
            });
            return step;
        },
        isStepVisible: function(step) {
            return this.findStepByName(step).isVisible;
        },
        setStepVisible: function(step, flag) {
            this.findStepByName(step).isVisible(flag);
        },
        getCurrentVisibleStep: function() {
            var step = null;
            $.each(this.steps, function(key, currentStep) {
                if (currentStep.isVisible()) {
                    step = currentStep;
                    return false;
                }
            });
            return step;
        },
        setStepEnabled: function(step, flag) {
            this.findStepByName(step).isEnabled = flag;
            this.refreshStepsNumbers();
        },
        refreshStepsNumbers: function() {
            var numb = 1;
            $.each(this.steps, function(key, item) {
                if (item.isEnabled) {
                    item.number(numb);
                    numb++;
                } else {
                    item.number(null);
                }
            });
        },
        getStepNumber: function(name) {
            return this.findStepByName(name).number;
        }
    };
});

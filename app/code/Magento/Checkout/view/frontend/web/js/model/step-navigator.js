/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery', 'ko', 'Magento_Customer/js/model/customer'], function($, ko, customer) {
    var customerIsLoggedIn = customer.isLoggedIn()();
    return {
        currentStep: null,
        steps: [
            {name: 'authentication', isVisible: ko.observable(!customerIsLoggedIn), order: 1, isEnabled: true},
            {name: 'billingAddress', isVisible: ko.observable(customerIsLoggedIn), order: 2, isEnabled: true},
            {name: 'shippingAddress', isVisible: ko.observable(false), order: 3, isEnabled: true},
            {name: 'shippingMethod', isVisible: ko.observable(false), order: 4, isEnabled: true},
            {name: 'paymentMethod', isVisible: ko.observable(false), order: 5, isEnabled: true},
            {name: 'review', isVisible: ko.observable(false), order: 6, isEnabled: true}
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
            var nextStepOrder = currentStep.order + 1;
            var nextStep;

            for (var item in this.steps) {
                nextStep = this.findStepByOrder(nextStepOrder);
                if (!nextStep || !nextStep.isEnabled) {
                    nextStepOrder++;
                    this.findStepByOrder(nextStepOrder);
                } else {
                    break;
                }
            }
            this.toStep(nextStep.name);
        },
        goBack: function() {
            var currentStep = this.getCurrentStep();
            var prevStepOrder = currentStep.order - 1;
            var previousStep;
            for (var item in this.steps) {
                previousStep = this.findStepByOrder(prevStepOrder);
                if (!previousStep || !previousStep.isEnabled) {
                    prevStepOrder--;
                    this.findStepByOrder(prevStepOrder);
                } else {
                    break;
                }
            }
            this.toStep(previousStep.name);
        },
        toStep: function(step) {
            if (step) {
                $.each(this.steps, function(key, step) {
                    step.isVisible(false);
                });
                this.findStepByName(step).isVisible(true);
            }
        },
        findStepByOrder: function(order) {
            var step = null;
            $.each(this.steps, function(key, item) {
                if (order == item.order) {
                    step = item;
                    return false;
                }
            });
            return step;
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
        setStepEnabled: function(step, flag) {
            this.findStepByName(step).isEnabled = flag;
        },
        getStepNumber: function(name) {
            var order = 1;
            $.each(this.steps, function(key, item) {
                if (item.name == name && item.isEnabled) {
                    return false;
                }
                if (item.isEnabled) {
                    order++;
                }
            });
            return order;
        }
    };
});

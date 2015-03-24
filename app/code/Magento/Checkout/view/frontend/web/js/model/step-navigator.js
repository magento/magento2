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
            {name: 'authentication', isVisible: ko.observable(!customerIsLoggedIn), order: 1},
            {name: 'billingAddress', isVisible: ko.observable(customerIsLoggedIn), order: 2},
            {name: 'shippingAddress', isVisible: ko.observable(false), order: 3},
            {name: 'shippingMethod', isVisible: ko.observable(false), order: 4},
            {name: 'paymentMethod', isVisible: ko.observable(false), order: 5},
            {name: 'review', isVisible: ko.observable(false), order: 6}
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
            var nextStep = null;
            $.each(this.steps, function(key, step) {
                var nextStepOrder = currentStep.order + 1;
                if (nextStepOrder == step.order) {
                    nextStep = step;
                }
            });
            this.toStep(nextStep.name);
        },
        goBack: function() {
            var currentStep = this.getCurrentStep();
            var previousStep = null;
            $.each(this.steps, function(key, step) {
                var prevStepOrder = currentStep.order - 1;
                if (prevStepOrder == step.order) {
                    previousStep = step;
                }
            });
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
        }
    };
});

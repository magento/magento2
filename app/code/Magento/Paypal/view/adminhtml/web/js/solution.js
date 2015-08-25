/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "Magento_Ui/js/lib/class",
    "Magento_Paypal/js/rule",
    "mageUtils",
    "underscore"
], function ($, Class, Rule, utils, _) {
    "use strict";
    return Class.extend({
        defaults: {
            /**
             * The event corresponding to the state change
             */
            systemEvent: 'change',
            /**
             * The rules applied after the page is loaded
             */
            afterLoadRules: [],
            /**
             * An attribute of the element responsible for the activation of the payment method (data attribute)
             */
            enableButton:   '[data-enable="payment"]',
            /**
             * An attribute of the element responsible for the activation of the Payflow Express (data attribute)
             */
            enableExpress:  '[data-enable="express"]',
            /**
             * An attribute of the element responsible for the activation of the Payflow Bml (data attribute)
             */
            enableBml:      '[data-enable="bml"]',
            /**
             * An attribute of the element responsible for the activation of the PayPal Bml (data attribute)
             */
            enableBmlPayPal:      '[data-enable="bml-api"]',
            /**
             * Templates element selectors
             */
            templates: {
                elementSelector: 'div.section-config tr[id$="${ $.identifier }"]:first'
            }
        },
        /**
         * Constructor
         */
        initialize: function (config, identifier) {
            this.initConfig(config);
            this.$self = this.createElement(identifier);
            return this;
        },
        /**
         * Initialization events
         */
        initEvents: function () {
            _.each(this.config.events, function (elementEvents, selector) {
                var solution = this,
                    selectorButton = solution.$self.find(selector),
                    $self = solution.$self,
                    events = elementEvents;
                selectorButton.on(solution.systemEvent, function (event) {
                    _.each(events, function (elementEvent, name) {
                        var predicate = elementEvent.predicate,
                            result = true;
                        if ($(this).val() === elementEvent.value) {
                            if (predicate.name) {
                                require([
                                    'Magento_Paypal/js/predicate/' + predicate.name
                                ], function (functionPredicate) {
                                    result = functionPredicate(solution, predicate.message, predicate.argument);
                                    if (result) {
                                        $self.trigger(name);
                                    } else {
                                        $self.trigger(predicate.event);
                                    }
                                });
                            } else {
                                $self.trigger(name);
                            }
                            }
                    }, this);
                    });
            }, this);
            return this;
        },
        /**
         * Adding event listeners
         */
        addListeners: function () {
            _.each(this.config.relations, function (rules, targetName) {
                var $target = this.createElement(targetName);
                _.each(rules, function (rule, name) {
                    var handler = new Rule({
                        name :name,
                        $target: $target,
                        $owner: this.$self,
                        data: {
                            buttonConfiguration: this.buttonConfiguration,
                            enableButton: this.enableButton,
                            enableExpress: this.enableExpress,
                            enableBml: this.enableBml,
                            enableBmlPayPal: this.enableBmlPayPal,
                            solutionsElements: this.solutionsElements,
                            argument: rule.argument
                        }
                    });
                    if (rule.event === ':load') {
                        this.afterLoadRules.push(handler);
                        return;
                    }
                    this.$self.on(rule.event, _.bind(handler.apply, handler));
                }, this);
            }, this);
            return this;
        },
        /**
         * Create a jQuery element according to selector
         */
        createElement: function (identifier) {
            if (':self' === identifier) {
                return this.$self;
            }
            var selector = utils.template(this.templates.elementSelector, {identifier: identifier});
            return $(selector);
        },
        /**
         * Assign solutions elements
         */
        setSolutionsElements: function (elements) {
            this.solutionsElements = elements;
            return this;
        }
    });
});

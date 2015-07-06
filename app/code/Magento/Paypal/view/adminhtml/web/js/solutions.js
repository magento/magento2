/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "Magento_Ui/js/lib/class",
    "Magento_Paypal/js/solution",
    "underscore"
], function ($, Class, Solution, _) {
    "use strict";
    return Class.extend({
        defaults: {
            /**
             * Initialized solutions
             */
            solutions: {},
            /**
             * The elements of created solutions
             */
            solutionsElements: {},
            /**
             * The selector element responsible for configuration of payment method (CSS class)
             */
            buttonConfiguration: '.button.action-configure'
        },
        /**
         * Constructor
         */
        initialize: function (config) {
            this.initConfig(config)
                .initSolutions();
            return this;
        },
        /**
         * Initialization and configuration solutions
         */
        initSolutions: function () {
            _.each(this.config.solutions, this.addSolution, this);
            this.initializeSolutions()
                .wipeButtonsConfiguration();
            _.each(this.solutions, this.applicationRules);
            return this;
        },
        /**
         * The creation and addition of the solution according to the configuration
         */
        addSolution: function (solution, identifier) {
            this.solutions[identifier] = new Solution({
                config: solution,
                buttonConfiguration: this.buttonConfiguration
            }, identifier);
            this.solutionsElements[identifier] = this.solutions[identifier].$self;
        },
        /**
         * Wiping buttons configuration of the payment method
         */
        wipeButtonsConfiguration: function () {
            $(this.buttonConfiguration).removeClass('disabled')
                .removeAttr('disabled');
        },
        /**
         * Application of the rules
         */
        applicationRules: function (solution) {
            _.each(solution.afterLoadRules, function (rule) {
                rule.apply();
            });
        },
        /**
         * Initialize solutions
         */
        initializeSolutions: function () {
            _.each(this.solutions, function (solution) {
                solution.setSolutionsElements(this.solutionsElements)
                    .initEvents()
                    .addListeners();
            }, this);
            return this;
        }
    });
});

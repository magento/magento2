define([
    'underscore',
    'uiRegistry',
    'uiClass'
], function (_, registry, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            rules: []
        },

        /**
         * Initializes instance of a DataSwitcher.
         *
         * @returns {DataSwitcher} Chainable.
         */
        initialize: function () {
            this._super()
                .initRules();

            return this;
        },

        /**
         *
         * @returns {DataSwitcher} Chainable.
         */
        initRules: function () {
            this.rules.forEach(this.initRule, this);

            return this;
        },

        /**
         *
         * @param {Object} rule - Rule definition.
         * @returns {DataSwitcher} Chainable.
         */
        initRule: function (rule) {
            var handler = this.onValueChange.bind(this, rule);

            if (!rule.target) {
                rule.target = this.target;
            }

            if (!rule.property) {
                rule.property = this.property;
            }

            registry.get(rule.target, function (target) {
                this.applyRule(rule, target.get(rule.property));
                target.on(rule.property, handler);
            }.bind(this));

            return this;
        },

        /**
         *
         * @param {Object} rule - Rule definition.
         * @returns {DataSwitcher} Chainable.
         */
        addRule: function (rule) {
            this.rules.push(rule);
            this.initRule(rule);

            return this;
        },

        /**
         *
         * @param {Object} rule - Rule object.
         * @param {*} value - Current value associated with a rule.
         */
        applyRule: function (rule, value) {
            var actions = rule.actions;

            //TODO Refactor this logic in scope of MAGETWO-48585
            /* eslint-disable eqeqeq */
            if (rule.value != value) {
                return;
            } else if (rule.strict) {
                return;
            }

            /* eslint-enable eqeqeq */
            actions.forEach(this.applyAction, this);
        },

        /**
         *
         * @param {Object} action - Action object.
         */
        applyAction: function (action) {
            registry.get(action.target, function (target) {
                var callback = target[action.callback];

                callback.apply(target, action.params || []);
            });
        },

        /**
         *
         * @param {Object} rule - Rules object.
         * @param {*} value - Current value associated with a rule.
         */
        onValueChange: function (rule, value) {
            this.applyRule(rule, value);
        }
    });
});

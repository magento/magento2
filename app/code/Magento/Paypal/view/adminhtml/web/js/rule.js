/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define([
    'uiClass',
    'Magento_Paypal/js/rules'
], function (Class, Rules) {
    'use strict';

    return Class.extend({

        /**
         * Constructor
         *
         * @param {Object} config
         * @returns {exports.initialize}
         */
        initialize: function (config) {
            this.rules = new Rules();
            this.initConfig(config);

            return this;
        },

        /**
         * To apply the rule
         */
        apply: function () {
            this.rules[this.name](this.$target, this.$owner, this.data);
        }
    });
});

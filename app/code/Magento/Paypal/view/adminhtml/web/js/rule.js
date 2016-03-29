/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

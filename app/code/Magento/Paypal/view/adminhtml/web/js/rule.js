/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiClass'
], function (Class) {
    'use strict';

    return Class.extend({

        /**
         * Constructor
         *
         * @param {Object} config
         * @returns {exports.initialize}
         */
        initialize: function (config) {
            this.initConfig(config);

            return this;
        },

        /**
         * To apply the rule
         */
        apply: function () {
            require([
                    'Magento_Paypal/js/rules/' + this.name
                ], function (applicableRule) {
                    applicableRule(this.$target, this.$owner, this.data);
                }.bind(this));
        }
    });
});

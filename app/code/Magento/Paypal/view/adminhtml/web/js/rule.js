/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "Magento_Ui/js/lib/class",
    "underscore"
], function (Class, _) {
    "use strict";
    return Class.extend({
        /**
         * Constructor
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

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_PaypalCaptcha/js/model/skipRefreshCaptcha'
], function (skipRefreshCaptcha) {
    'use strict';

    var payflowProMethodMixin = {
        /**
         * @override
         */
        placeOrder: function () {
            skipRefreshCaptcha.skip(true);
            this._super();
        }
    };

    return function (payflowProMethod) {
        return payflowProMethod.extend(payflowProMethodMixin);
    };
});

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/customer-email-validator'
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';

        additionalValidators.registerValidator(agreementValidator);

        return Component.extend({});
    }
);

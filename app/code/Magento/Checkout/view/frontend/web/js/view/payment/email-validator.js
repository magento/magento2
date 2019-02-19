/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

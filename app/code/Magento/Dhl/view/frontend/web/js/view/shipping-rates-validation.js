/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Magento_Dhl/js/model/shipping-rates-validator',
    'Magento_Dhl/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    dhlShippingRatesValidator,
    dhlShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('dhl', dhlShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('dhl', dhlShippingRatesValidationRules);

    return Component;
});

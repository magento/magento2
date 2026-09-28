/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/freeshipping',
    '../../model/shipping-rates-validation-rules/freeshipping'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    freeshippingShippingRatesValidator,
    freeshippingShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('freeshipping', freeshippingShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('freeshipping', freeshippingShippingRatesValidationRules);

    return Component;
});

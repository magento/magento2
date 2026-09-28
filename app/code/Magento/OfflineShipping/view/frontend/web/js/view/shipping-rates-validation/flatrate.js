/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/flatrate',
    '../../model/shipping-rates-validation-rules/flatrate'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    flatrateShippingRatesValidator,
    flatrateShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('flatrate', flatrateShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('flatrate', flatrateShippingRatesValidationRules);

    return Component;
});

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/tablerate',
    '../../model/shipping-rates-validation-rules/tablerate'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    tablerateShippingRatesValidator,
    tablerateShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('tablerate', tablerateShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('tablerate', tablerateShippingRatesValidationRules);

    return Component;
});

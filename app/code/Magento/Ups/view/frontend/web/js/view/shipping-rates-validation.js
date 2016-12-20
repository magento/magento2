/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Magento_Ups/js/model/shipping-rates-validator',
    'Magento_Ups/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    upsShippingRatesValidator,
    upsShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('ups', upsShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('ups', upsShippingRatesValidationRules);

    return Component;
});

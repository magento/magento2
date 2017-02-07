/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

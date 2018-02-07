/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../../model/shipping-rates-validator/tablerate',
        '../../model/shipping-rates-validation-rules/tablerate'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        tablerateShippingRatesValidator,
        tablerateShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('tablerate', tablerateShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('tablerate', tablerateShippingRatesValidationRules);
        return Component;
    }
);

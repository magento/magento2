/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/select-payment-method': {
                'Magento_SalesRule/js/action/select-payment-method-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor': {
                'Magento_SalesRule/js/model/shipping-save-processor-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Magento_SalesRule/js/model/place-order-mixin': true
            }
        }
    }
};

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            checkoutBalance:    'Magento_Customer/js/checkout-balance',
            address:            'Magento_Customer/address',
            setPassword:        'Magento_Customer/set-password'
        }
    },
    deps: [
        'mage/validation/dob-rule'
    ]
};
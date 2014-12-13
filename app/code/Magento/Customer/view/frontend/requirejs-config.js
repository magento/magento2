/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
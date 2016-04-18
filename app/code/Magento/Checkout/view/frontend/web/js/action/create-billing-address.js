/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/address-converter'
    ],
    function(addressConverter) {
        "use strict";
        return function(addressData) {
            return addressConverter.formAddressDataToQuoteAddress(addressData);
        };
    }
);

/**
 * Copyright © 2015 Magento. All rights reserved.
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
            var address = addressConverter.formAddressDataToQuoteAddress(addressData);
            return address;
        };
    }
);

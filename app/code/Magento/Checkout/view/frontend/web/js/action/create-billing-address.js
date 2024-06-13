/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/address-converter'
], function (addressConverter) {
    'use strict';

    return function (addressData) {
        var address = addressConverter.formAddressDataToQuoteAddress(addressData);

        /**
         * Returns new customer billing address type.
         *
         * @returns {String}
         */
        address.getType = function () {
            return 'new-customer-billing-address';
        };

        return address;
    };
});

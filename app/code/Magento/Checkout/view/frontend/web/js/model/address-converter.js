/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Customer/js/model/customer/address'
    ],
    function($, Address) {
        'use strict';
        return {
            /**
             * Convert address form data to Address object
             * @param formData
             * @returns {*}
             */
            formAddressDataToQuoteAddress: function(formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData);
                if (typeof addressData.street == 'object') {
                    addressData.street = this.objectToString(addressData.street, ', ');
                }
                return Address(addressData);
            },

            /**
             * Convert object to string with delimiter
             * @param object
             * @param delimiter
             * @returns {string}
             */
            objectToString: function(object, delimiter) {
                var streetConcatenated = '';
                $.each(object, function(key, item) {
                    if (item.length > 0) {
                        streetConcatenated += item + delimiter;
                    }
                });
                return streetConcatenated.slice(0, -(delimiter.length));
            }
        };
    }
);

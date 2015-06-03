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
        var countryData = window.checkoutConfig.countryData;
        return {
            /**
             * Convert address form data to Address object
             * @param formData
             * @returns {address}
             */
            formAddressDataToQuoteAddress: function(formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData);
                if (typeof addressData.street == 'object') {
                    addressData.street = this.objectToString(addressData.street, ', ');
                }

                addressData.region = {
                    region_id: null,
                    region_code: null,
                    region: null
                };
                if (addressData.region_id) {
                    var region = countryData[addressData.country_id]['regions'][addressData.region_id];
                    if (region) {
                        addressData.region.region_id = addressData['region_id'];
                        addressData.region.region_code = region['code'];
                        addressData.region.region = region['name'];
                    }
                }
                delete addressData.region_id;
                return Address(addressData);
            },

            formDataProviderToQuoteAddress: function(formProviderData, formIndex) {
                var addressData = {};
                $.each(formProviderData, function(path, value) {
                    var pathComponents = path.split('.');
                    pathComponents.splice(pathComponents.indexOf(formIndex), 1);
                    pathComponents.reverse();
                    var dataObject = {};
                    $.each(pathComponents, function(index, pathPart) {
                        if (index == 0) {
                            dataObject[pathPart] = value;
                        } else {
                            var parent = {};
                            parent[pathPart] = dataObject;
                            dataObject = parent;
                        }
                    });
                    $.extend(true, addressData, dataObject);
                });
                return this.formAddressDataToQuoteAddress(addressData);
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

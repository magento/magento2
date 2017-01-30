/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/new-customer-address',
        'Magento_Customer/js/customer-data',
        'mage/utils/objects'
    ],
    function($, address, customerData, mageUtils) {
        'use strict';
        var countryData = customerData.get('directory-data');

        return {
            /**
             * Convert address form data to Address object
             * @param {Object} formData
             * @returns {Object}
             */
            formAddressDataToQuoteAddress: function(formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData),
                    region,
                    regionName = addressData.region;
                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this.objectToArray(addressData.street);
                }

                addressData.region = {
                    region_id: addressData.region_id,
                    region_code: addressData.region_code,
                    region: regionName
                };

                if (addressData.region_id
                    && countryData()[addressData.country_id]
                    && countryData()[addressData.country_id]['regions']
                ) {
                    region = countryData()[addressData.country_id]['regions'][addressData.region_id];
                    if (region) {
                        addressData.region.region_id = addressData['region_id'];
                        addressData.region.region_code = region['code'];
                        addressData.region.region = region['name'];
                    }
                }
                delete addressData.region_id;

                return address(addressData);
            },

            /**
             * Convert Address object to address form data
             * @param {Object} address
             * @returns {Object}
             */
            quoteAddressToFormAddressData: function (address) {
                var self = this;
                var output = {};

                if ($.isArray(address.street)) {
                    var streetObject = {};
                    address.street.forEach(function(value, index) {
                        streetObject[index] = value;
                    });
                    address.street = streetObject;
                }

                $.each(address, function (key) {
                    if (address.hasOwnProperty(key) && !$.isFunction(address[key])) {
                        output[self.toUnderscore(key)] = address[key];
                    }
                });
                return output;
            },

            toUnderscore: function (string) {
                return string.replace(/([A-Z])/g, function($1){return "_"+$1.toLowerCase();});
            },

            formDataProviderToFlatData: function(formProviderData, formIndex) {
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
                return addressData;
            },

            /**
             * Convert object to array
             * @param {Object} object
             * @returns {Array}
             */
            objectToArray: function (object) {
                var convertedArray = [];

                $.each(object, function (key) {
                    return typeof object[key] === 'string' ? convertedArray.push(object[key]) : false;
                });

                return convertedArray.slice(0);
            },

            addressToEstimationAddress: function (address) {
                var self = this;
                var estimatedAddressData = {};

                $.each(address, function (key) {
                    estimatedAddressData[self.toUnderscore(key)] = address[key];
                });
                return this.formAddressDataToQuoteAddress(estimatedAddressData);
            }
        };
    }
);

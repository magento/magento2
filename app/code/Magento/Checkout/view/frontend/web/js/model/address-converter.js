/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/new-customer-address',
        'mage/utils/objects'
    ],
    function($, address, mageUtils) {
        'use strict';
        var countryData = window.checkoutConfig.countryData;

        return {
            /**
             * Convert address form data to Address object
             * @param {Object} formData
             * @returns {Object}
             */
            formAddressDataToQuoteAddress: function(formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData),
                    region;

                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this.objectToArray(addressData.street);
                }

                addressData.region = {
                    region_id: null,
                    region_code: null,
                    region: null
                };

                if (addressData.region_id) {
                    region = countryData[addressData.country_id]['regions'][addressData.region_id];
                    if (region) {
                        addressData.region.region_id = addressData['region_id'];
                        addressData.region.region_code = region['code'];
                        addressData.region.region = region['name'];
                    }
                }
                delete addressData.region_id;

                return address(addressData);
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
                    return object[key].length ? convertedArray.push(object[key]) : false;
                });

                return convertedArray.slice(0);
            }
        };
    }
);

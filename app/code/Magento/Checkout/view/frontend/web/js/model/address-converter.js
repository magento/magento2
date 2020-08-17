/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/new-customer-address',
    'Magento_Customer/js/customer-data',
    'mage/utils/objects',
    'underscore'
], function ($, address, customerData, mageUtils, _) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return {
        /**
         * Convert address form data to Address object
         *
         * @param {Object} formData
         * @returns {Object}
         */
        formAddressDataToQuoteAddress: function (formData) {
            // clone address form data to new object
            var addressData = $.extend(true, {}, formData),
                region,
                regionName = addressData.region;

            if (mageUtils.isObject(addressData.street)) {
                addressData.street = this.objectToArray(addressData.street);
            }

            addressData.region = {
                'region_id': addressData['region_id'],
                'region_code': addressData['region_code'],
                region: regionName
            };

            if (addressData['region_id'] &&
                countryData()[addressData['country_id']] &&
                countryData()[addressData['country_id']].regions
            ) {
                region = countryData()[addressData['country_id']].regions[addressData['region_id']];

                if (region) {
                    addressData.region['region_id'] = addressData['region_id'];
                    addressData.region['region_code'] = region.code;
                    addressData.region.region = region.name;
                }
            } else if (
                !addressData['region_id'] &&
                countryData()[addressData['country_id']] &&
                countryData()[addressData['country_id']].regions
            ) {
                addressData.region['region_code'] = '';
                addressData.region.region = '';
            }
            delete addressData['region_id'];

            if (addressData['custom_attributes']) {
                addressData['custom_attributes'] = _.map(
                    addressData['custom_attributes'],
                    function (value, key) {
                        return {
                            'attribute_code': key,
                            'value': value
                        };
                    }
                );
            }

            return address(addressData);
        },

        /**
         * Convert Address object to address form data.
         *
         * @param {Object} addrs
         * @returns {Object}
         */
        quoteAddressToFormAddressData: function (addrs) {
            var self = this,
                output = {},
                streetObject,
                customAttributesObject;

            $.each(addrs, function (key) {
                if (addrs.hasOwnProperty(key) && !$.isFunction(addrs[key])) {
                    output[self.toUnderscore(key)] = addrs[key];
                }
            });

            if ($.isArray(addrs.street)) {
                streetObject = {};
                addrs.street.forEach(function (value, index) {
                    streetObject[index] = value;
                });
                output.street = streetObject;
            }

            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            if ($.isArray(addrs.customAttributes)) {
                customAttributesObject = {};
                addrs.customAttributes.forEach(function (value) {
                    customAttributesObject[value.attribute_code] = value.value;
                });
                output.custom_attributes = customAttributesObject;
            }
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            return output;
        },

        /**
         * @param {String} string
         */
        toUnderscore: function (string) {
            return string.replace(/([A-Z])/g, function ($1) {
                return '_' + $1.toLowerCase();
            });
        },

        /**
         * @param {Object} formProviderData
         * @param {String} formIndex
         * @return {Object}
         */
        formDataProviderToFlatData: function (formProviderData, formIndex) {
            var addressData = {};

            $.each(formProviderData, function (path, value) {
                var pathComponents = path.split('.'),
                    dataObject = {};

                pathComponents.splice(pathComponents.indexOf(formIndex), 1);
                pathComponents.reverse();
                $.each(pathComponents, function (index, pathPart) {
                    var parent = {};

                    if (index == 0) { //eslint-disable-line eqeqeq
                        dataObject[pathPart] = value;
                    } else {
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

        /**
         * @param {Object} addrs
         * @return {*|Object}
         */
        addressToEstimationAddress: function (addrs) {
            var self = this,
                estimatedAddressData = {};

            $.each(addrs, function (key) {
                estimatedAddressData[self.toUnderscore(key)] = addrs[key];
            });

            return this.formAddressDataToQuoteAddress(estimatedAddressData);
        }
    };
});

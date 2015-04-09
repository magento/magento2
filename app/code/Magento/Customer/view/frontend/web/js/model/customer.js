/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/model/addresslist',
        './customer/address'
    ],
    function($, ko, storage, addressList, address) {
        "use strict";
        var isLoggedIn = ko.observable(window.isLoggedIn),
            customerData = {};

        if (isLoggedIn()) {
            customerData = window.customerData;
            if (Object.keys(customerData).length) {
                $.each(customerData.addresses, function (key, item) {
                    addressList.add(new address(item));
                });
            }
        } else {
            customerData = {};
        }
        return {
            customerData: customerData,
            customerDetails: {},
            isLoggedIn: function() {
                return isLoggedIn;
            },
            setIsLoggedIn: function (flag) {
                isLoggedIn(flag);
            },
            getBillingAddressList: function () {
                return addressList.getAddresses();
            },
            getShippingAddressList: function () {
                return addressList.getAddresses();
            },
            setDetails: function (fieldName, value) {
                if (fieldName) {
                    this.customerDetails[fieldName] = value;
                }
            },
            getDetails: function (fieldName) {
                if (fieldName) {
                    if (this.customerDetails.hasOwnProperty(fieldName)) {
                        return this.customerDetails[fieldName];
                    }
                    return undefined;
                } else {
                    return this.customerDetails;
                }
            },
            addCustomerAddress: function (address) {
                var fields = ['customer_id', 'region', 'country_id', 'street', 'company', 'telephone', 'fax', 'postcode', 'city', 'firstname', 'lastname', 'middlename', 'prefix', 'suffix', 'vat_id', 'default_billing', 'default_shipping'],
                    customerAddress = {},
                    hasSameAddress = 0,
                    field,
                    existingAddress;

                if (!this.customerData.addresses) {
                    this.customerData.addresses = [];
                }

                for (field in address) {
                    if (address.hasOwnProperty(field)) {
                        if (fields.indexOf(field) > 0) {
                            customerAddress[field] = address[field];
                        }
                    }
                }
                for (existingAddress in this.customerData.addresses) {
                    if (this.customerData.addresses.hasOwnProperty(existingAddress)) {
                        hasSameAddress += this.customerData.addresses[existingAddress] === customerAddress ? 1 : 0;
                    }
                }
                if (hasSameAddress === 0) {
                    this.customerData.addresses.push(customerAddress);
                }
            }
        };
    }
);

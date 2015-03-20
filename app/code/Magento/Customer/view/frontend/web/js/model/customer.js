/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/model/addresslist',
        './customer/address'
    ],
    function($, ko, storage, addressList, address) {
        var isLoggedIn = ko.observable(window.isLoggedIn);
        var customerData = {};

        if (isLoggedIn()) {
            customerData = window.customerData;
            if (Object.keys(customerData).length) {
                $.each(customerData.addresses, function (key, item) {
                    addressList.add(new address(item));
                });
            }
        }
        return {
            customerData: customerData,
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
            }
        }
    }
);

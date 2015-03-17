/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mage/storage'], function(storage) {
    var isLoggedIn = false;
    return {
        customerData: [],
        load: function () {
            this.customerData = window.customerData;
        },
        isLoggedIn: function() {
            return isLoggedIn;
        },
        setIsLoggedIn: function (flag) {
            isLoggedIn = flag;
        },
        getBillingAddressList: function () {
            return this.customerData.addresses;
        },
        getShippingAddressList: function () {
            return this.customerData.addresses;
        }
    }
});

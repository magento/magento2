/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['../model/order'],
    function(order) {
        return function(billingAddressId, shipToSame, formKey) {
            return order.setBillingAddress(billingAddressId, shipToSame);
        }
    }
);

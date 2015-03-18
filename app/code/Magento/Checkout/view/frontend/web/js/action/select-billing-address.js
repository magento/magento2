/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['../model/quote'],
    function(quote) {
        return function(billingAddressId, shipToSame, formKey) {
            return quote.setBillingAddress(billingAddressId, shipToSame);
        }
    }
);

/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['../model/quote'], function(quote, shippingMethodCode) {
    return quote.setShippingMethod(shippingMethodCode);
});

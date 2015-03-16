/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['../model/order'], function(order, shippingMethodCode) {
    return order.setShippingMethod(shippingMethodCode);
});

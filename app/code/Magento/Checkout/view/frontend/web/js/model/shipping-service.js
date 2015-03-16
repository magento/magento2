/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mage/storage', ], function(storage) {
    return {
        getAvailableShippingMethods: function(order) {
            return storage.get('checkout/shippingRates');
        }
    }
});

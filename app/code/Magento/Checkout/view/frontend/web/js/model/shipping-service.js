/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mage/storage', '../model/quote'], function(storage) {
    return {
        getAvailableShippingMethods: function(quote) {
            return storage.get('rest/default/V1/carts/'+ quote.getQuoteId() + '/shipping-methods');
        }
    }
});

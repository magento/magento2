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
        '../model/quote',
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function(quote, storage, errorList) {
        return function() {
            storage.put(
                'rest/default/V1/carts/' + quote.getQuoteId() + '/order'
            ).done(
                function() {
                    window.location.replace('/checkout/onepage/success/');
                }
            ).error(
                function(response) {
                    var error = $.parseJSON(response.responseText);
                    errorList.add(error.message);
                }
            );
        }
    }
);

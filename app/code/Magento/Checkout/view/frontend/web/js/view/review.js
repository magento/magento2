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
        'Magento_Ui/js/form/component',
        '../model/quote',
        'Magento_Customer/js/model/customer'
    ],
    function (Component, quote, customer) {
        var itemsBefore = [];
        var itemsAfter = [];

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review',
                isLoggedIn: customer.isLoggedIn(),
                quoteHasPaymentMethod: quote.hasPaymentMethod(),
                itemsBefore: itemsBefore,
                itemsAfter: itemsAfter,
                getItems: function() {},
                getAgreementsTemplate: function() {}
            }
        });
    }
);

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
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/action/place-order',
    ],
    function (Component, quote, customer, navigator, orderAction) {
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
                getAgreementsTemplate: function() {},
                isVisible: navigator.isReviewVisible(),
                placeOrder: function() {
                    orderAction();
                }
            }
        });
    }
);

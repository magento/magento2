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
        'mage/url',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/action/place-order'
    ],
    function (Component, quote, url, navigator, orderAction) {
        var stepName = 'review';
        var itemsBefore = [];
        var itemsAfter = [];
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review',
                stepNumber: function(){
                    return navigator.getStepNumber(stepName);
                },
                quoteHasPaymentMethod: quote.getPaymentMethod(),
                itemsBefore: itemsBefore,
                itemsAfter: itemsAfter,
                getItems: function() {
                    return window.cartItems;
                },
                getAgreementsTemplate: function() {},
                isVisible: navigator.isStepVisible(stepName),
                cartUrl: url.build('checkout/cart/'),
                placeOrder: function() {
                    orderAction();
                }
            }
        });
    }
);

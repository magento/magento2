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
        'Magento_Checkout/js/action/place-order',
    ],
    function (Component, quote, url, navigator, orderAction) {
        var itemsBefore = [];
        var itemsAfter = [];
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review',
                quoteHasPaymentMethod: quote.getPaymentMethod(),
                itemsBefore: itemsBefore,
                itemsAfter: itemsAfter,
                getItems: function() {},
                getAgreementsTemplate: function() {},
                isVisible: navigator.isStepVisible('review'),
                cartUrl: url.build('checkout/cart/'),
                placeOrder: function() {
                    orderAction();
                }
            }
        });
    }
);

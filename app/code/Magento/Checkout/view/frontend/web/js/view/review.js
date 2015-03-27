/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        'Magento_Checkout/js/model/review',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, url, navigator, orderAction, review, priceUtils) {
        var stepName = 'review';
        var itemsBefore = [];
        var itemsAfter = [];
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review'
            },
            stepNumber: navigator.getStepNumber(stepName),
            quoteHasPaymentMethod: quote.getPaymentMethod(),
            itemsBefore: itemsBefore,
            itemsAfter: itemsAfter,
            getItems: function() {
                return quote.getItems();
            },
            getAgreementsTemplate: function() {},
            isVisible: navigator.isStepVisible(stepName),
            cartUrl: url.build('checkout/cart/'),
            placeOrder: function() {
                orderAction();
            },
            // get recalculated totals when all data set
            getTotals: review.getTotals(),
            getFormattedPrice: function (price) {
                //todo add format data further
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price)
            }
        });
    }
);

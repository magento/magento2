/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'uiComponent',
        '../model/quote',
        'mage/url',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/review'
    ],
    function (Component, quote, url, navigator, review) {
        "use strict";
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
            getColHeaders: function() {
                return ['name', 'price', 'qty', 'subtotal'];
            },
            getAgreementsTemplate: function() {},
            isVisible: navigator.isStepVisible(stepName),
            cartUrl: url.build('checkout/cart/'),
            // get recalculated totals when all data set
            getTotals: review.getTotals()
        });
    }
);

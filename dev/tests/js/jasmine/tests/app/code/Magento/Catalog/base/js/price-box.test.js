/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Catalog/js/price-box'
], function ($) {
    'use strict';

    describe('Magento_Catalog/js/price-box', function () {
        var priceBox,
            qty;

        beforeEach(function () {
            qty = $('<input id="qty" value="3">').appendTo(document.body);
            priceBox = $(
                '<div data-role="priceBox">' +
                    '<span data-price-type="finalPrice" data-price-amount="100"></span>' +
                    '<span data-price-type="basePrice" data-price-amount="100"></span>' +
                '</div>'
            ).appendTo(document.body);
        });

        afterEach(function () {
            priceBox.priceBox('destroy').remove();
            qty.remove();
        });

        it('applies the tier price during initialization', function () {
            priceBox.priceBox({
                priceConfig: {
                    prices: {
                        finalPrice: {
                            amount: 100,
                            adjustments: {}
                        },
                        basePrice: {
                            amount: 100,
                            adjustments: {}
                        }
                    },
                    tierPrices: [{
                        qty: 2,
                        price: 20,
                        basePrice: 20
                    }]
                }
            });

            expect(priceBox.priceBox('instance').cache.displayPrices.finalPrice.amount).toBe(20);
            expect(priceBox.priceBox('instance').cache.displayPrices.basePrice.amount).toBe(20);
        });

        it('keeps the original price when the quantity does not qualify for a tier price', function () {
            qty.val(1);
            priceBox.priceBox({
                priceConfig: {
                    prices: {
                        finalPrice: {
                            amount: 100,
                            adjustments: {}
                        }
                    },
                    tierPrices: [{
                        qty: 2,
                        price: 20
                    }]
                }
            });

            expect(priceBox.priceBox('instance').cache.displayPrices.finalPrice.amount).toBe(100);
        });

        it('recalculates the tier price when the quantity input event is triggered', function () {
            qty.val(1);
            priceBox.priceBox({
                priceConfig: {
                    prices: {
                        finalPrice: {
                            amount: 100,
                            adjustments: {}
                        }
                    },
                    tierPrices: [{
                        qty: 2,
                        price: 20
                    }]
                }
            });

            qty.val(3).trigger('input');

            expect(priceBox.priceBox('instance').cache.displayPrices.finalPrice.amount).toBe(20);
        });
    });
});

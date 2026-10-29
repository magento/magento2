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

        it('applies tier price during initialization when tier prices are configured', function () {
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

        it('keeps the original price when quantity does not qualify for a tier price', function () {
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

        it('recalculates tier price when quantity is changed', function () {
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

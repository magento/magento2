/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['Magento_Catalog/js/utils/percentage-price-calculator'], function (percentagePriceCalculator) {
    'use strict';

    var basePrice = 100,
        negativeBasePrice = -10,
        decimalBasePrice = '100,1',
        zeroBasePrice = 0;

    describe('Check valid calculation', function () {
        it('5%', function () {
            expect(percentagePriceCalculator(basePrice, '5%')).toBe('95.00');
        });
        it('0%', function () {
            expect(percentagePriceCalculator(basePrice, '0%')).toBe('100.00');
        });
        it('100%', function () {
            expect(percentagePriceCalculator(basePrice, '100%')).toBe('0.00');
        });
        it('110%', function () {
            expect(percentagePriceCalculator(basePrice, '110%')).toBe('0.00');
        });
        it('5.5%', function () {
            expect(percentagePriceCalculator(basePrice, '5.5%')).toBe('94.50');
        });
        it('.5%', function () {
            expect(percentagePriceCalculator(basePrice, '.5%')).toBe('99.50');
        });
        it('-7%', function () {
            expect(percentagePriceCalculator(basePrice, '-7%')).toBe('107.00');
        });
    });

    describe('Check invalid input calculation', function () {
        it('invalid with %', function () {
            expect(percentagePriceCalculator(basePrice, '7p%')).toBe('');
            expect(percentagePriceCalculator(basePrice, '-%')).toBe('');
        });
        it('without %', function () {
            expect(percentagePriceCalculator(basePrice, '7p')).toBe('7p');
            expect(percentagePriceCalculator(basePrice, '0')).toBe('0');
            expect(percentagePriceCalculator(basePrice, 'qwe')).toBe('qwe');
        });
        it('just %', function () {
            expect(percentagePriceCalculator(basePrice, '%')).toBe('');
        });
        it('empty', function () {
            expect(percentagePriceCalculator(basePrice, '')).toBe('');
        });
    });

    describe('Other', function () {
        it('negative base price', function () {
            expect(percentagePriceCalculator(negativeBasePrice, '10%')).toBe('-9.00');
        });
        it('decimal base price', function () {
            expect(percentagePriceCalculator(decimalBasePrice, '10%')).toBe('90.09');
        });
        it('zero base price', function () {
            expect(percentagePriceCalculator(zeroBasePrice, '10%')).toBe('0.00');
        });
    });
});

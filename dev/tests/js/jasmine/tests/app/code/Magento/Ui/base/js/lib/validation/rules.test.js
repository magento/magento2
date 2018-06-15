/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/lib/validation/rules'
], function (rules) {
    'use strict';

    describe('Magento_Ui/js/lib/validation/rules', function () {
        describe('"range-words" method', function () {
            it('Check on empty value', function () {
                var value = '',
                    params = [1,3];

                expect(rules['range-words'].handler(value, params)).toBe(false);
            });

            it('Check on redundant words', function () {
                var value = 'a b c d',
                    params = [1,3];

                expect(rules['range-words'].handler(value, params)).toBe(false);
            });

            it('Check with three words', function () {
                var value = 'a b c',
                    params = [1,3];

                expect(rules['range-words'].handler(value, params)).toBe(true);
            });

            it('Check with one word', function () {
                var value = 'a',
                    params = [1,3];

                expect(rules['range-words'].handler(value, params)).toBe(true);
            });
        });
        describe('"validate-number" method', function () {
            it('Check on empty value', function () {
                var value = '';

                expect(rules['validate-number'].handler(value)).toBe(true);
            });

            it('Check on integer', function () {
                var value = '125';

                expect(rules['validate-number'].handler(value)).toBe(true);
            });

            it('Check on float', function () {
                var value = '1000.50';

                expect(rules['validate-number'].handler(value)).toBe(true);
            });

            it('Check on formatted float', function () {
                var value = '1,000,000.50';

                expect(rules['validate-number'].handler(value)).toBe(true);
            });

            it('Check on not a number', function () {
                var value = 'string';

                expect(rules['validate-number'].handler(value)).toBe(false);
            });
        });
    });
});

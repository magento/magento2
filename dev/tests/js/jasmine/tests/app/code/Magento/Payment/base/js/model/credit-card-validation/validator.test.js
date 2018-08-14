/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/validate',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function ($) {
    'use strict';

    describe('Magento_Payment/js/model/credit-card-validation/validator', function () {

        it('Check credit card expiration year validator.', function () {
            var year = new Date().getFullYear();

            expect($.validator.methods['validate-card-year']('1234')).toBeFalsy();
            expect($.validator.methods['validate-card-year']('')).toBeFalsy();
            expect($.validator.methods['validate-card-year']((year - 1).toString())).toBeFalsy();
            expect($.validator.methods['validate-card-year']((year + 1).toString())).toBeTruthy();
        });
    });
});

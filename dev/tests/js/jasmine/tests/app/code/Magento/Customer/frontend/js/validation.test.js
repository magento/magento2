/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_Customer/js/validation'
], function ($) {
    'use strict';

    describe('Testing customer DOB validation to tolerate zeroes in the single digit dates', function () {
        var params,
            dataProvider;

        dataProvider = [
            {
                format: 'M/d/Y',
                date: '09/2/18',
                expects: true
            },
            {
                format: 'M/DD/Y',
                date: '09/2/18',
                expects: false
            }
        ];

        dataProvider.forEach(function (data) {
            it('Test date validation for format ' + data.format, function () {
                params = {
                    'dateFormat': data.format
                };
                expect($.validator.methods['validate-date']
                    .call($.validator.prototype, data.date, null, params)).toEqual(data.expects);
            });
        });
    });
});

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

    describe('Testing Customer/view/frontend/web/js/validation.js', function () {
        var params,
            dataProvider;

        dataProvider = [
            {
                format: 'dd.MM.yy.',
                date: '09.02.18.',
                expects: true
            },
            {
                format: 'd/MM/y',
                date: '9/02/2018',
                expects: true
            },
            {
                format: 'MM/dd/yy',
                date: '02/09/18',
                expects: true
            },
            {
                format: 'M/d/yy',
                date: '2/9/18',
                expects: true
            },
            {
                format: 'yy-MM-dd',
                date: '18-02-09',
                expects: true
            },
            {
                format: 'dd.MM.y.',
                date: '09.02.2018.',
                expects: true
            },
            {
                format: 'y. MM. dd.',
                date: '2018. 02. 09.',
                expects: true
            },
            {
                format: 'd/MM/yy',
                date: '9/02/18',
                expects: true
            },
            {
                format: 'dd-MM-yy',
                date: '09-02-18',
                expects: true
            },
            {
                format: 'dd/MM/yy',
                date: '09/02/18',
                expects: true
            },
            {
                format: 'dd.MM.y',
                date: '09.02.2018',
                expects: true
            },
            {
                format: 'd. MM. yy',
                date: '9. 02. 18',
                expects: true
            },
            {
                format: 'dd/MM/y',
                date: '09/02/2018',
                expects: true
            },
            {
                format: 'd.MM.y',
                date: '9.02.2018',
                expects: true
            },
            {
                format: 'd.M.yy',
                date: '9.2.18',
                expects: true
            },
            {
                format: 'd.MM.yy г.',
                date: '9.02.18 г.',
                expects: true
            },
            {
                format: 'dd.M.yy',
                date: '09.2.18',
                expects: true
            },
            {
                format: 'y-MM-dd',
                date: '2018-02-09',
                expects: true
            },
            {
                format: 'd.M.yy.',
                date: '9.2.18.',
                expects: true
            },
            {
                format: 'd.M.y',
                date: '9.2.2018',
                expects: true
            },
            {
                format: 'd/M/y',
                date: '9/2/2018',
                expects: true
            },
            {
                format: 'yy/M/d',
                date: '19/2/9',
                expects: true
            },
            {
                format: 'd/M/yy',
                date: '9/2/18',
                expects: true
            },
            {
                format: 'y/M/d',
                date: '2018/2/9',
                expects: true
            },
            {
                format: 'y/MM/dd',
                date: '2018/02/09',
                expects: true
            },
            {
                format: 'yy. M. d.',
                date: '18. 2. 9.',
                expects: true
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

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'jquery',
    'ko',
    'jquery/ui'
], function (Squire, $, ko) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Catalog/js/product/view/product-ids': ko.observableArray([])
        },
        form = $(
            '<form>' +
            '<input type="hidden" name="product" value="1">' +
            '</form>'
        ),
        productIdResolver;

    beforeAll(function (done) {

        injector.mock(mocks);
        injector.require(
            [
                'Magento_Catalog/js/product/view/product-ids-resolver'
            ], function (resolver) {
                productIdResolver = resolver;
                done();
            }
        );
    });

    describe('Magento_Catalog/js/product/view/product-ids-resolver', function () {
        var dataProvider = [
            {
                ids: [],
                expected: ['1']
            },
            {
                ids: ['2', '3'],
                expected: ['2', '3', '1']
            },
            {
                ids: ['3', '1', '5'],
                expected: ['3', '1', '5']
            }
        ];

        dataProvider.forEach(function (data) {
            it('resolved product id\'s', function () {
                mocks['Magento_Catalog/js/product/view/product-ids'](data.ids);
                expect(productIdResolver(form)).toEqual(data.expected);
            });
        });
    });
});

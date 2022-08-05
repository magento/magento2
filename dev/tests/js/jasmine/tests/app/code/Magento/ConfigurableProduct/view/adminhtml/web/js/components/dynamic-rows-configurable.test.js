/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function ($, DynamicRowsConf, DynamicRows) {
    'use strict';

    describe('Magento_ConfigurableProduct/js/components/dynamic-rows-configurable', function () {
        var model;

        beforeEach(function () {
            model = new DynamicRowsConf(new DynamicRows({
                isEmpty: jasmine.createSpy().and.returnValue(1),
                isShowAddProductButton: jasmine.createSpy().and.returnValue(1)
            }));

        });

        it('Verify processingUnionInsertDat method', function () {
            var expectedData = [],
                mockData = [
                {
                    attributes: 'Color: dsfsd',
                    sku: 'Conf&-sdfs'
                },
                {
                    attributes: 'Color: sdfs',
                    sku: 'Conf-dsfsd'
                }
            ],
                sourceMock = {
                    get: jasmine.createSpy().and.returnValue(['code1', 'code2']),
                    set: jasmine.createSpy().and.callFake(function (path, row) {
                        expectedData.push(row);
                    })
                };

            model.source = sourceMock;
            model.processingUnionInsertData(mockData);
            expect(model.source.get).toHaveBeenCalled();
            expect(expectedData[1].sku).toBe('Conf&-sdfs');
        });

    });
});

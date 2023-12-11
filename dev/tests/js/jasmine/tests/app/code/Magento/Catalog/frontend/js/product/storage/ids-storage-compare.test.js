/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire',
    'underscore'
], function ($, Squire, _) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue({})
            },
            'Magento_Catalog/js/product/storage/ids-storage': {}
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/storage/ids-storage-compare'], function (insance) {
            obj = _.extend({}, insance);
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Catalog/js/product/storage/ids-storage-compare', function () {
        describe('"providerDataHandler" method', function () {
            it('check calls "prepareData" and "add" method', function () {
                var data = {
                    property: 'value'
                };

                obj.prepareData = jasmine.createSpy().and.returnValue(data);
                obj.add = jasmine.createSpy();

                obj.providerDataHandler(data);
                expect(obj.prepareData).toHaveBeenCalledWith(data);
                expect(obj.add).toHaveBeenCalledWith(data);
            });
        });
        describe('"prepareData" method', function () {
            it('check returned value', function () {
                var data = {
                    '1': {
                        id: '1'
                    }
                },
                result = obj.prepareData(data);

                expect(typeof result[data['1'].id]).toBe('object');
                expect(typeof result[data['1'].id]['added_at']).toBe('number');
                expect(result[data['1'].id]['product_id']).toBe(data['1'].id);
            });
        });
    });
});

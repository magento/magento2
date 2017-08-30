/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Ui/js/lib/core/events': {
                on: jasmine.createSpy()
            },
            'Magento_Ui/js/lib/registry/registry': {
                /** Method stub. */
                get: function () {
                    return {
                        get: jasmine.createSpy(),
                        set: jasmine.createSpy()
                    };
                },
                create: jasmine.createSpy(),
                set: jasmine.createSpy(),
                async: jasmine.createSpy()
            },
            '/mage/utils/wrapper': jasmine.createSpy()
        },
        obj,
        dataScope = 'dataScope';

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Ui/js/form/element/multiselect'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                dataScope: dataScope
            });

            done();
        });
    });

    describe('Magento_Ui/js/form/element/multiselect', function () {
        describe('"setPrepareToSendData" method', function () {
            it('Check method call with empty array as parameter.', function () {
                expect(obj.setPrepareToSendData([])).toBeUndefined();
                expect(obj.source.set).toHaveBeenCalledWith(dataScope + '-prepared-for-send', '');
            });

            it('Check method call with undefined as parameter.', function () {

                expect(obj.setPrepareToSendData(undefined)).toBeUndefined();
                expect(obj.source.set).toHaveBeenCalledWith(dataScope + '-prepared-for-send', '');
            });

            it('Check method call with array with data as parameter.', function () {
                expect(obj.setPrepareToSendData(['1', '2', '3'])).toBeUndefined();
                expect(obj.source.set).toHaveBeenCalledWith(dataScope + '-prepared-for-send', ['1', '2', '3']);
            });
        });
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define(['squire', 'underscore'], function (Squire, _) {
    'use strict';

    var injector = new Squire(),
        storage = {
            requestConfig: {
                method: 'GET'
            }
        },
        mocks = {
            'Magento_Ui/js/core/renderer/layout': jasmine.createSpy(),
            'Magento_Ui/js/lib/core/element/element': {
                extend: function (child) {
                    var uiElement = function () {
                        _.extend(this, child.defaults);
                    };

                    _.extend(uiElement.prototype, child);
                    return uiElement;
                }
            },
            'Magento_Ui/js/lib/registry/registry': {
                async: jasmine.createSpy().and.returnValue(function (callback) {
                    callback(storage);
                }),
                create: jasmine.createSpy(),
                set: jasmine.createSpy(),
                get: function (query, callback) {
                    callback(storage);
                    return storage;
                }
            },
            'Magento_Ui/js/grid/data-storage': jasmine.createSpy()
        },
        model;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Ui/js/grid/provider'], function (Constr) {
            model = new Constr();
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Ui/js/grid/provider', function () {
        describe('"updateRequestConfig" method', function () {
            it('Check that storage "requestConfig" is updated.', function () {
                model.updateRequestConfig({method: 'PATCH'});
                expect(storage.requestConfig.method).toEqual('PATCH');
            });
        });
        describe('"onError" method', function () {
            it('test onError.', function () {
                model.trigger = jasmine.createSpy();
                model.set = jasmine.createSpy();
                model.onError({statusText: 'error'});
                expect(model.set).toHaveBeenCalledWith('lastError', true);
                expect(model.trigger).toHaveBeenCalledWith('reloaded');
                expect(model.firstLoad).toEqual(false);
                expect(model.triggerDataReload).toEqual(false);
            });
        });
    });
});

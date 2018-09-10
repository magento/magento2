/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/single-checkbox-use-config', function () {
        var injector = new Squire(),
            mocks = {
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
            dataScope = 'dataScope',
            params = {
                provider: 'provName',
                name: '',
                index: '',
                dataScope: dataScope
            },
            model;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/single-checkbox-use-config',
                'knockoutjs/knockout-es5'
            ], function (Constr) {
                model = new Constr(params);
                done();
            });
        });

        describe('initObservable method', function () {
            it('check for chainable', function () {
                expect(model.initObservable({})).toEqual(model);
            });
            it('check for validation', function () {
                spyOn(model, 'observe').and.returnValue(model);
                expect(model.initObservable()).toEqual(model);
                expect(model.validation).toEqual({});
            });
        });

        describe('toggleElement method', function () {
            it('check with isUseDefault false', function () {
                spyOn(model, 'isUseDefault').and.returnValue(false);
                spyOn(model, 'isUseConfig').and.returnValue(false);
                expect(model.toggleElement()).toEqual(undefined);
                expect(model.disabled()).toEqual(false);
                expect(model.source.set).toHaveBeenCalledWith('data.use_default.' + model.index, 0);
            });
            it('check with isUseDefault true', function () {
                spyOn(model, 'isUseDefault').and.returnValue(true);
                spyOn(model, 'isUseConfig').and.returnValue(false);
                expect(model.toggleElement()).toEqual(undefined);
                expect(model.disabled()).toEqual(true);
                expect(model.source.set).toHaveBeenCalledWith('data.use_default.' + model.index, 1);
            });
        });
    });
});

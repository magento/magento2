/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/boolean', function () {
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
            model,
            dataScope = 'dataScope';

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/boolean',
                'knockoutjs/knockout-es5'
            ], function (Constr) {
                model = new Constr({
                    provider: 'provName',
                    name: '',
                    index: '',
                    dataScope: dataScope
                });

                done();
            });
        });

        describe('getInitialValue method', function () {
            it('check for default', function () {
                expect(model.getInitialValue()).toEqual(false);
            });
            it('check with default value', function () {
                model.default = 1;
                expect(model.getInitialValue()).toEqual(false);
            });
            it('check with value', function () {
                model.value(1);
                expect(model.getInitialValue()).toEqual(true);
            });
            it('check with value and default', function () {
                model.default = 1;
                model.value(0);
                expect(model.getInitialValue()).toEqual(false);
            });
        });
        describe('onUpdate method', function () {
            it('check for setUnique call', function () {
                spyOn(model, 'setUnique');
                model.hasUnique = true;
                model.onUpdate();
                expect(model.setUnique).toHaveBeenCalled();
            });
            it('check for setUnique not call', function () {
                spyOn(model, 'setUnique');
                model.onUpdate();
                expect(model.setUnique).not.toHaveBeenCalled();
            });
        });
    });
});

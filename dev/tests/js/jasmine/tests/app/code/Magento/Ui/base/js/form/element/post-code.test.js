/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/post-code', function () {
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
            dataScope = 'post-code';

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/post-code',
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

        describe('update method', function () {
            it('check for default', function () {
                var value = 'Value',
                    country = {
                        indexedOptions: {
                            'Value': {
                                'is_zipcode_optional': true
                            }
                        }
                    };

                spyOn(mocks['Magento_Ui/js/lib/registry/registry'], 'get').and.returnValue(country);
                model.update(value);
                expect(mocks['Magento_Ui/js/lib/registry/registry'].get).toHaveBeenCalled();
                expect(model.error()).toEqual(false);
                expect(model.required()).toEqual(false);
            });
        });
    });
});

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
            it('makes field optional when there is no corresponding country', function () {
                var value = 'Value';

                model.countryOptions = {};

                model.update(value);

                expect(model.required()).toEqual(false);
            });

            it('makes field optional when post code is optional for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_zipcode_optional': true
                    }
                };

                model.update(value);

                expect(model.required()).toEqual(false);
            });

            it('removes field required validation when post code is optional for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_zipcode_optional': true
                    }
                };

                model.update(value);

                expect(model.validation['required-entry']).toBeFalsy();
            });

            it('makes field required when post code is required for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_zipcode_optional': false
                    }
                };

                model.update(value);

                expect(model.required()).toEqual(true);
            });

            it('sets field required validation when post code is required for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_zipcode_optional': false
                    }
                };

                model.update(value);

                expect(model.validation['required-entry']).toEqual(true);
            });
        });
    });
});

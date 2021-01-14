/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/region', function () {
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
                'Magento_Ui/js/form/element/region',
                'knockoutjs/knockout-es5',
                'Magento_Ui/js/lib/knockout/extender/observable_array'
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

        describe('initialize method', function () {
            it('Hides region field when it should be hidden for default country', function () {
                model.countryOptions = {
                    'DefaultCountryCode': {
                        'is_default': true,
                        'is_region_visible': false
                    },
                    'NonDefaultCountryCode': {
                        'is_region_visible': true
                    }
                };

                model.initialize();

                expect(model.visible()).toEqual(false);
            });

            it('Shows region field when it should be visible for default country', function () {
                model.countryOptions = {
                    'CountryCode': {
                        'is_default': true,
                        'is_region_visible': true
                    },
                    'NonDefaultCountryCode': {
                        'is_region_visible': false
                    }
                };

                model.initialize();

                expect(model.visible()).toEqual(true);
            });
        });

        describe('update method', function () {
            it('makes field optional when there is no corresponding country', function () {
                var value = 'Value';

                model.countryOptions = {};

                model.update(value);

                expect(model.required()).toEqual(false);
            });

            it('makes field optional when region is optional for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': false
                    }
                };

                model.update(value);

                expect(model.required()).toEqual(false);
            });

            it('removes field required validation when region is optional for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': false
                    }
                };

                model.update(value);

                expect(model.validation['required-entry']).toBeFalsy();
            });

            it('makes field required when region is required for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': true
                    }
                };

                model.update(value);

                expect(model.required()).toEqual(true);
            });

            it('sets field required validation when region is required for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': true
                    }
                };

                model.update(value);

                expect(model.validation['required-entry']).toEqual(true);
            });

            it('keeps region visible by default', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {}
                };

                model.update(value);

                expect(model.visible()).toEqual(true);
            });

            it('hides region field when it should be hidden for certain country', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_visible': false
                    }
                };

                model.update(value);

                expect(model.visible()).toEqual(false);
            });

            it('makes field optional when validation should be skipped', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': true
                    }
                };

                model.skipValidation = true;
                model.update(value);

                expect(model.required()).toEqual(false);
            });

            it('removes field validation when validation should be skipped', function () {
                var value = 'Value';

                model.countryOptions = {
                    'Value': {
                        'is_region_required': true
                    }
                };

                model.skipValidation = true;
                model.update(value);

                expect(model.validation['required-entry']).toBeFalsy();
            });
        });
    });
});

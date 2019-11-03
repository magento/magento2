/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'Magento_Catalog/js/components/use-parent-settings/select',
    'Magento_Catalog/js/components/use-parent-settings/toggle-disabled-mixin'
], function (_, select, mixin) {
    'use strict';

    var CustomInput = mixin(select),
        defaultProperties = {
            name: 'uiSelect',
            dataScope: '',
            provider: 'provider',
            service: true
        },
        obj;

    describe('toggle-disabled-mixin structure tests', function () {
        var defaultContext = require.s.contexts._;

        obj = new CustomInput(defaultProperties);

        it('mixin is present in RequireJs config', function () {
            var requireJsConfig = defaultContext.config
                .config.mixins['Magento_Catalog/js/components/use-parent-settings/select'];

            expect(
                requireJsConfig['Magento_Catalog/js/components/use-parent-settings/toggle-disabled-mixin']
            ).toBe(true);
        });

        it('Check for useParent property', function () {
            expect(obj.hasOwnProperty('useParent')).toBeTruthy();
            expect(typeof obj.useParent).toEqual('boolean');
            expect(obj.useParent).toEqual(false);
        });

        it('Check for useDefaults property', function () {
            expect(obj.hasOwnProperty('useDefaults')).toBeTruthy();
            expect(typeof obj.useDefaults).toEqual('boolean');
            expect(obj.useDefaults).toEqual(false);
        });

        it('Check for toggleDisabled method', function () {
            expect(obj.toggleDisabled).toBeDefined();
            expect(typeof obj.toggleDisabled).toEqual('function');
        });

        it('Check for saveUseDefaults method', function () {
            expect(obj.saveUseDefaults).toBeDefined();
            expect(typeof obj.saveUseDefaults).toEqual('function');
        });

        it('Check for setInitialValue method', function () {
            expect(obj.setInitialValue).toBeDefined();
            expect(typeof obj.setInitialValue).toEqual('function');
        });

        it('Check for toggleUseDefault method', function () {
            expect(obj.toggleUseDefault).toBeDefined();
            expect(typeof obj.toggleUseDefault).toEqual('function');
        });
    });

    describe('toggle-disabled-mixin functionality', function () {
        var dataProvider = [
                {
                    defaults: {
                        useParent: false,
                        useDefaults: false
                    },
                    expected: {
                        disabled: false
                    }
                },
                {
                    defaults: {
                        useParent: true,
                        useDefaults: false
                    },
                    expected: {
                        disabled: true
                    }
                },
                {
                    defaults: {
                        useParent: false,
                        useDefaults: true
                    },
                    expected: {
                        disabled: true
                    }
                },
                {
                    defaults: {
                        useParent: true,
                        useDefaults: true
                    },
                    expected: {
                        disabled: true
                    }
                }
            ];

        dataProvider.forEach(function (state) {
            describe(JSON.stringify(state.defaults), function () {

                beforeEach(function () {
                    obj = new CustomInput(
                        _.extend(defaultProperties, state.defaults)
                    );
                });

                it('Check disabled state', function () {
                    expect(obj.disabled()).toEqual(state.expected.disabled);
                });

                it('Check checked state', function () {
                    expect(obj.isUseDefault()).toEqual(state.defaults.useDefaults);
                });

                it('Check of using parent settings', function () {
                    obj.toggleDisabled(true);
                    expect(obj.isUseDefault()).toEqual(state.defaults.useDefaults);
                    expect(obj.disabled()).toEqual(true);
                });

                it('Check of using self settings', function () {
                    obj.toggleDisabled(false);
                    expect(obj.isUseDefault()).toEqual(state.defaults.useDefaults);
                    expect(obj.disabled()).toEqual(obj.isUseDefault());
                });
            });
        });
    });
});

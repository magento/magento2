/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_Ui/js/form/components/multiline'
], function (registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/multiline', function () {
        var obj,
            dataScope = 'data',
            providerName = 'provider',
            prepareDataProvider = function (value) { // jscs:ignore jsDoc
                registry.set(providerName, {
                    /** Stub */
                    on: function () {},

                    /** Stub */
                    set: function () {},

                    /** Stub */
                    get: function () {
                        return value;
                    }
                });
            };

        describe('Verify process of preparing value for Multiline options', function () {
            it('Check _prepareValue method', function () {
                obj = new Constr({
                    _prepareValue: jasmine.createSpy()
                });

                expect(obj._prepareValue).toHaveBeenCalled();
            });

            it('Check array preparation', function () {
                var value = ['some_array'];

                prepareDataProvider(value);
                obj = new Constr({
                    provider: providerName,
                    dataScope: dataScope
                });

                expect(obj.value().slice(0)).toEqual(value);
            });

            it('Check preparation of string value with line breaks', function () {
                var value = 'first\n\nthird';

                prepareDataProvider(value);
                obj = new Constr({
                    provider: providerName,
                    dataScope: dataScope
                });

                expect(obj.value()).toEqual(['first', '', 'third']);
            });
        });
    });
});

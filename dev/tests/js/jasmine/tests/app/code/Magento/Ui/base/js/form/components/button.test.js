/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function (Constr, registry) {
    'use strict';

    describe('Magento_Ui/js/form/components/button', function () {
        var params = {
            provider: 'provName',
            name: '',
            index: '',
            actions: [
                {
                    actionName: 'someAction',
                    targetName: 'some_target_component'
                },
                {
                    targetName: 'some_other_target_component'
                }
            ]
        },
            obj = new Constr(params);

        registry.set('provName', {
            /** Stub */
            has: function () {},

            /** Stub */
            async: function () {}
        });

        describe('initialize method', function () {
            it('check for existing', function () {
                expect(obj).toBeDefined();
            });

            it('check for chainable', function () {
                spyOn(obj, '_setClasses').and.returnValue(obj);
                spyOn(obj, '_setButtonClasses').and.returnValue(obj);
                expect(obj.initialize(params)).toEqual(obj);
            });
        });

        describe('"action" method', function () {
            it('check that actions has been applied', function () {
                spyOn(obj, 'applyAction');
                obj.action();
                expect(obj.applyAction).toHaveBeenCalled();
            });
        });

        describe('"applyAction" method', function () {
            it('check that called creation node function if target component does not exist', function () {
                spyOn(registry, 'has').and.returnValue(false);

                obj.applyAction(obj.actions[0]);

                expect(registry.has).toHaveBeenCalledWith(obj.actions[0].targetName);
            });

            it('check that target component is sought in registry', function () {
                var target = jasmine.createSpy();

                spyOn(registry, 'async').and.returnValue(target);
                obj.applyAction(obj.actions[0]);

                expect(registry.async).toHaveBeenCalledWith(obj.actions[0].targetName);
            });
        });

        describe('"_setClasses" method', function () {
            it('check for chainable', function () {
                expect(obj._setClasses()).toEqual(obj);
            });

            it('check for incorrect class set', function () {
                obj.additionalClasses = 1;

                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(1);
            });

            it('check for empty additional class', function () {
                var expectedResult = {};

                obj.additionalClasses = '';

                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(expectedResult);
            });

            it('check for one class in additional', function () {
                var extendObject = {
                    simple: true
                };

                obj.additionalClasses = 'simple';
                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(extendObject);
            });

            it('check for one class with spaces in additional', function () {
                var extendObject = {
                    simple: true
                };

                obj.additionalClasses = ' simple ';
                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(extendObject);
            });

            it('check for multiple classes in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true
                };

                obj.additionalClasses = 'simple example';
                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(extendObject);
            });

            it('check for multiple classes with spaces in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true
                };

                obj.additionalClasses = ' simple example ';
                expect(obj._setClasses()).toEqual(obj);
                expect(obj.additionalClasses).toEqual(extendObject);
            });
        });

        describe('"_setButtonClasses" method', function () {
            it('check for chainable', function () {
                expect(obj._setButtonClasses()).toEqual(obj);
            });

            it('check for incorrect class set', function () {
                obj.buttonClasses = 1;

                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(1);
            });

            it('check for empty additional class', function () {
                var expectedResult = {
                    'action-basic': !obj.displayAsLink,
                    'action-additional': obj.displayAsLink
                };

                obj.buttonClasses = '';

                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(expectedResult);
            });

            it('check for one class in additional', function () {
                var extendObject = {
                    simple: true,
                    'action-basic': !obj.displayAsLink,
                    'action-additional': obj.displayAsLink
                };

                obj.buttonClasses = 'simple';
                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(extendObject);
            });

            it('check for one class with spaces in additional', function () {
                var extendObject = {
                    simple: true,
                    'action-basic': !obj.displayAsLink,
                    'action-additional': obj.displayAsLink
                };

                obj.buttonClasses = ' simple ';
                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(extendObject);
            });

            it('check for multiple classes in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true,
                    'action-basic': !obj.displayAsLink,
                    'action-additional': obj.displayAsLink
                };

                obj.buttonClasses = 'simple example';
                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(extendObject);
            });

            it('check for multiple classes with spaces in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true,
                    'action-basic': !obj.displayAsLink,
                    'action-additional': obj.displayAsLink
                };

                obj.buttonClasses = ' simple example ';
                expect(obj._setButtonClasses()).toEqual(obj);
                expect(obj.buttonClasses).toEqual(extendObject);
            });
        });
    });
});

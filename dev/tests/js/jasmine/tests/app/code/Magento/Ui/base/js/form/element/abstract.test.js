/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    describe('Magento_Ui/js/form/element/abstract', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'abstract'
            };
            model = new Abstract(params);
            model.source = jasmine.createSpyObj('model.source', ['set']);
        });

        describe('initialize method', function () {
            it('check for existing', function () {
                expect(model).toBeDefined();
            });
            it('check for chainable', function () {
                spyOn(model, 'setInitialValue').and.returnValue(model);
                spyOn(model, '_setClasses').and.returnValue(model);
                expect(model.initialize(params)).toEqual(model);
            });
        });

        describe('initObservable method', function () {
            it('check for chainable', function () {
                spyOn(model, 'observe').and.returnValue(model);
                expect(model.initObservable()).toEqual(model);
            });
            it('check for validation', function () {
                spyOn(model, 'observe').and.returnValue(model);
                expect(model.initObservable()).toEqual(model);
                expect(model.validation).toEqual({});
            });
        });
        describe('setInitialValue method', function () {
            it('check for chainable', function () {
                expect(model.setInitialValue()).toEqual(model);
            });
            it('check for set value', function () {
                var expectedValue = 1;

                spyOn(model, 'getInitialValue').and.returnValue(expectedValue);
                expect(model.setInitialValue()).toEqual(model);
                expect(model.getInitialValue).toHaveBeenCalled();
                expect(model.value()).toEqual(expectedValue);
            });
        });
        describe('_setClasses method', function () {
            it('check for chainable', function () {
                expect(model._setClasses()).toEqual(model);
            });
            it('check for incorrect class set', function () {
                model.additionalClasses = 1;

                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(1);
            });
            it('check for empty additional class', function () {
                var expectedResult = {
                    _required: model.required,
                    _warn: model.warn,
                    _error: model.error,
                    _disabled: model.disabled
                };

                model.additionalClasses = '';

                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(expectedResult);
            });
            it('check for one class in additional', function () {
                var extendObject = {
                    simple: true,
                    _required: model.required,
                    _warn: model.warn,
                    _error: model.error,
                    _disabled: model.disabled
                };

                model.additionalClasses = 'simple';
                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(extendObject);
            });
            it('check for one class with spaces in additional', function () {
                var extendObject = {
                    simple: true,
                    _required: model.required,
                    _warn: model.warn,
                    _error: model.error,
                    _disabled: model.disabled
                };

                model.additionalClasses = ' simple ';
                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(extendObject);
            });
            it('check for multiple classes in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true,
                    _required: model.required,
                    _warn: model.warn,
                    _error: model.error,
                    _disabled: model.disabled
                };

                model.additionalClasses = 'simple example';
                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(extendObject);
            });
            it('check for multiple classes with spaces in additional', function () {
                var extendObject = {
                    simple: true,
                    example: true,
                    _required: model.required,
                    _warn: model.warn,
                    _error: model.error,
                    _disabled: model.disabled
                };

                model.additionalClasses = ' simple example ';
                expect(model._setClasses()).toEqual(model);
                expect(model.additionalClasses).toEqual(extendObject);
            });
        });
        describe('getInitialValue method', function () {
            it('check with empty value', function () {
                expect(model.getInitialValue()).toEqual('');
            });
            it('check with default value', function () {
                model.default = 1;
                expect(model.getInitialValue()).toEqual('');
            });
            it('check with value', function () {
                var expected = 1;

                model.value(expected);
                expect(model.getInitialValue()).toEqual(expected);
            });
            it('check with value and default', function () {
                var expected = 1;

                model.default = 2;
                model.value(expected);
                expect(model.getInitialValue()).toEqual(expected);
            });
        });
        describe('setVisible method', function () {
            it('check value by default', function () {
                expect(model.visible()).toBeTruthy();
            });
            it('check for true/false parameters', function () {
                expect(model.setVisible(false)).toEqual(model);
                expect(model.visible()).toBeFalsy();
                expect(model.setVisible(true)).toEqual(model);
                expect(model.visible()).toBeTruthy();
            });
        });
        describe('getPreview method', function () {
            it('check with absent value', function () {
                expect(model.value()).toEqual('');
            });
            it('check with value', function () {
                var expected = 1;

                model.value(expected);
                expect(model.value()).toEqual(expected);
            });
        });
        describe('hasAddons method', function () {
            it('check with absent addbefore and addafter', function () {
                expect(model.hasAddons()).toEqual(undefined);
            });
            it('check with different addbefore and addafter', function () {
                model.addafter = false;
                expect(model.hasAddons()).toEqual(false);
                model.addafter = true;
                expect(model.hasAddons()).toEqual(true);
                model.addbefore = true;
                model.addafter = true;
                expect(model.hasAddons()).toEqual(true);
                model.addbefore = true;
                model.addafter = false;
                expect(model.hasAddons()).toEqual(true);
                model.addbefore = false;
                model.addafter = false;
                expect(model.hasAddons()).toEqual(false);
                model.addbefore = false;
                model.addafter = true;
                expect(model.hasAddons()).toEqual(true);
            });
        });
        describe('hasChanged method', function () {
            it('check without changes', function () {
                expect(model.hasChanged()).toEqual(false);
            });
            it('check with changed value', function () {
                model.value(1);
                expect(model.hasChanged()).toEqual(true);
            });
            it('check with hidden', function () {
                model.visible(false);
                expect(model.hasChanged()).toEqual(false);
            });
            it('check with hidden and changed value', function () {
                model.visible(false);
                model.value(1);

                expect(model.hasChanged()).toEqual(false);
            });
        });
        describe('hasData method', function () {
            it('check with empty value', function () {
                expect(model.hasData()).toEqual(false);
            });
            it('check with value', function () {
                model.value(1);
                expect(model.hasData()).toEqual(true);
            });
        });
        describe('reset method', function () {
            it('check with default value', function () {
                model.reset();
                expect(model.value()).toEqual(model.initialValue);
            });
            it('check with changed value', function () {
                model.value(1);
                model.reset();
                expect(model.value()).toEqual(model.initialValue);
            });
        });
        describe('clear method', function () {
            it('check with default value', function () {
                expect(model.clear()).toEqual(model);
                expect(model.value()).toEqual('');
            });
            it('check with changed value', function () {
                model.value(1);
                expect(model.clear()).toEqual(model);
                expect(model.value()).toEqual('');
            });
        });
        describe('validate method', function () {
            it('check with default value', function () {
                var expected = {
                    valid: false,
                    target: model
                };

                model.validation = 'validate-no-empty';
                expect(model.validate()).toEqual(expected);
            });
            it('check with valid value', function () {
                var expected = {
                    valid: true,
                    target: model
                };

                model.validation = 'validate-no-empty';
                model.value('valid');
                expect(model.validate()).toEqual(expected);
            });
            it('check if element hidden and value not valid', function () {
                var expected = {
                    valid: true,
                    target: model
                };

                model.validation = 'validate-no-empty';
                model.visible(false);
                expect(model.validate()).toEqual(expected);
            });
            it('check if element hidden and value valid', function () {
                var expected = {
                    valid: true,
                    target: model
                };

                model.validation = 'validate-no-empty';
                model.visible(false);
                model.value('valid');
                expect(model.validate()).toEqual(expected);
            });
        });
        describe('onUpdate method', function () {
            it('check for method call', function () {
                spyOn(model, 'bubble');
                spyOn(model, 'hasChanged');
                spyOn(model, 'validate');
                model.onUpdate();
                expect(model.bubble).toHaveBeenCalled();
                expect(model.hasChanged).toHaveBeenCalled();
                expect(model.validate).toHaveBeenCalled();
            });
        });
    });
});

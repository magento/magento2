/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'uiRegistry',
    'Magento_Catalog/js/components/product-ui-select',
    'ko',
    'jquery'
], function (_, registry, Constr, ko, $) {
    'use strict';

    describe('Magento_Catalog/js/components/product-ui-select', function () {
        var obj,
            originaljQueryAjax;

        beforeEach(function () {
            originaljQueryAjax = $.ajax;

            obj = new Constr({
                name: 'productUiSelect',
                dataScope: '',
                provider: 'provider',
                options: [],
                value: ''
            });
        });

        afterEach(function () {
            $.ajax = originaljQueryAjax;
        });

        describe('"validateInitialValue" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('validateInitialValue')).toBeDefined();
            });

            it('Should call not call ajax if value is empty', function () {
                $.ajax = jasmine.createSpy();

                spyOn(obj, 'validationLoading');
                spyOn(obj, 'value').and.returnValue('');

                expect(obj.validateInitialValue()).toBeUndefined();

                expect($.ajax).not.toHaveBeenCalled();
                expect(obj.validationLoading).toHaveBeenCalledWith(false);
            });

            it('Should call ajax if value is not empty', function () {
                var successCallback,
                    completeCallback,
                    responseData = {
                    label: 'hello world',
                    value: 'hello world'
                };

                $.ajax = jasmine.createSpy().and.callFake(function (request) {
                    successCallback = request.success.bind(obj);
                    completeCallback = request.complete.bind(obj);
                });

                spyOn(obj, 'validationLoading');
                spyOn(obj, 'value').and.returnValue('hello world');
                spyOn(obj, 'options');
                spyOn(obj, 'setCaption');

                expect(obj.validateInitialValue()).toBeUndefined();

                successCallback(responseData);
                completeCallback();

                expect($.ajax).toHaveBeenCalled();

                expect(obj.validationLoading).toHaveBeenCalledWith(false);
                expect(obj.loadedOption).toBe(responseData);
                expect(obj.options).toHaveBeenCalledWith([responseData]);

                expect(obj.setCaption).toHaveBeenCalled();
            });
        });
    });
});

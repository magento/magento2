/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/knockout/bindings/color-picker'
], function (ko, $) {
    'use strict';

    var $input,
        originaljQuery,
        originaljQueryInit,
        originaljQuerySpectrum;

    beforeEach(function () {
        originaljQuery = $;
        originaljQueryInit = $.fn.init;
        originaljQuerySpectrum = $.fn.spectrum;
    });

    afterEach(function () {
        $ = originaljQuery;
        $.fn.init = originaljQueryInit;
        $.fn.spectrum = originaljQuerySpectrum;
    });

    describe('Colorpicker binding', function () {
        it('Should call spectrum on $input with disabled configuration if view model disabled', function () {
            var valueAccessor = jasmine.createSpy().and.returnValue({}),
                viewModel = {
                    disabled: jasmine.createSpy().and.returnValue(true)
                };

            $.fn.spectrum = jasmine.createSpy();
            $input = jasmine.createSpy();

            ko.bindingHandlers.colorPicker.init($input, valueAccessor, null, viewModel);

            expect($.fn.spectrum).toHaveBeenCalledWith({
                disabled: true
            });

            $.fn.init = jasmine.createSpy().and.returnValue($.fn);

            ko.bindingHandlers.colorPicker.init($input, valueAccessor, null, viewModel);

            expect($.fn.init).toHaveBeenCalledWith($input, undefined);
        });

        it('Should call spectrum on $input with extra configuration if view model enabled', function () {
            var value = {
                    configStuffInHere: true
                },
                valueAccessor = jasmine.createSpy().and.returnValue(value),
                viewModel = {
                    disabled: jasmine.createSpy().and.returnValue(false)
                };

            $.fn.spectrum = jasmine.createSpy();
            $input = jasmine.createSpy();

            ko.bindingHandlers.colorPicker.init($input, valueAccessor, null, viewModel);

            expect(value.change).toEqual(jasmine.any(Function));
            expect(value.hide).toEqual(jasmine.any(Function));
            expect(value.show).toEqual(jasmine.any(Function));
            expect(value.change).toBe(value.hide);

            expect($.fn.spectrum).toHaveBeenCalledWith(value);

            $.fn.init = jasmine.createSpy().and.returnValue($.fn);

            ko.bindingHandlers.colorPicker.init($input, valueAccessor, null, viewModel);

            expect($.fn.init).toHaveBeenCalledWith($input, undefined);
        });
    });
});

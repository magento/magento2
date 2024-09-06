/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/knockout/bindings/dimVisible'
], function (ko, $) {
    'use strict';

    describe('dimVisible binding', function () {
        var elementToHide = $('<fieldset class="hidden-fields"></fieldset>');

        beforeEach(function () {
            $(document.body).append(elementToHide);
        });

        afterEach(function () {
            elementToHide.remove();
        });

        it('Check that html element is hidden based on flag value', function () {
            let value = false,
                valueAccessor = jasmine.createSpy().and.returnValue(value);

            ko.bindingHandlers.dimVisible.init(elementToHide, valueAccessor);
            expect(
                elementToHide.attr('style').indexOf('visibility: hidden; height: 0px; position: absolute;') !== -1
            ).toBeTrue();
        });

        it('Check that html element is displayed based on flag value', function () {
            let value = true,
                valueAccessor = jasmine.createSpy().and.returnValue(value);

            ko.bindingHandlers.dimVisible.init(elementToHide, valueAccessor);
            expect(
                elementToHide.attr('style').indexOf('visibility: visible; height: auto; position: relative;') !== -1
            ).toBeTrue();
        });

        it('Check that html element is updated based on flags changing value', function () {
            let valueFalse = false,
                valueFalseAccessor = jasmine.createSpy().and.returnValue(valueFalse),
                valueTrue = true,
                valueTrueAccessor = jasmine.createSpy().and.returnValue(valueTrue);

            ko.bindingHandlers.dimVisible.update(elementToHide, valueFalseAccessor);
            expect(
                elementToHide.attr('style').indexOf('visibility: hidden; height: 0px; position: absolute;') !== -1
            ).toBeTrue();

            ko.bindingHandlers.dimVisible.update(elementToHide, valueTrueAccessor);
            expect(
                elementToHide.attr('style').indexOf('visibility: visible; height: auto; position: relative;') !== -1
            ).toBeTrue();
        });
    });
});

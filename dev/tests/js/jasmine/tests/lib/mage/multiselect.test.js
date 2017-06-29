/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */

define([
    'jquery',
    'mage/multiselect'
], function ($) {
    'use strict';

    describe('Test for mage/multiselect jQuery plugin', function () {
        var element = '<select></select>',
            instance,
            options = {
                'nextPageUrl': '/url',
                'selectedValues': [1]
            };

        beforeEach(function () {
            $('body').append(element);
            instance = $(element).multiselect2(options);
        });

        afterEach(function () {
            $(element).remove();
        });

        it('multiselect2 fn exists', function () {
            expect($.mage.multiselect2).toBeDefined();
        });

        it('multiselect2 methods check', function () {
            expect(instance.data('mage-multiselect2').onScroll).toBeDefined();
            expect(instance.data('mage-multiselect2').onKeyUp).toBeDefined();
            expect(instance.data('mage-multiselect2').onCheck).toBeDefined();
            expect(instance.data('mage-multiselect2').onError).toBeDefined();
            expect(instance.data('mage-multiselect2').onOptionsChange).toBeDefined();
        });

        it('multiselect2 options check', function () {
            var url = instance.multiselect2('option', 'nextPageUrl'),
                values = instance.multiselect2('option', 'selectedValues');

            expect(url).not.toBeUndefined();
            expect(values).not.toBeUndefined();
            expect(values instanceof Array).toBeTruthy();
        });

        it('multiselect2 loadOptions success case', function () {
            spyOn(instance.data('mage-multiselect2'), 'appendOptions').and.callFake(function () {
                return true;
            });

            $.get = jasmine.createSpy().and.callFake(function () {
                var d = $.Deferred();

                d.resolve({
                    'success': true
                });

                return d.promise();
            });

            instance.data('mage-multiselect2').loadOptions();

            expect($.get).toHaveBeenCalled();
            expect(instance.data('mage-multiselect2').appendOptions).toHaveBeenCalled();
        });

        it('multiselect2 loadOptions negative case', function () {
            var errorMessage = 'Something went wrong';

            spyOn(instance.data('mage-multiselect2'), 'onError').and.callFake(function () {
                return true;
            });

            $.get = jasmine.createSpy().and.callFake(function () {
                var d = $.Deferred();

                d.resolve({
                    'success': false,
                    'errorMessage': errorMessage
                });

                return d.promise();
            });

            instance.data('mage-multiselect2').loadOptions();

            expect($.get).toHaveBeenCalled();
            expect(instance.data('mage-multiselect2').onError).toHaveBeenCalledWith(errorMessage);
        });

        it('multiselect2 onKeyUp check', function () {
            spyOn(instance.data('mage-multiselect2'), 'getSearchCriteria').and.returnValue('some_string');
            spyOn(instance.data('mage-multiselect2'), 'setFilter');
            spyOn(instance.data('mage-multiselect2'), 'loadOptions');

            instance.data('mage-multiselect2').onKeyUp();

            expect(instance.data('mage-multiselect2').setFilter).toHaveBeenCalled();
            expect(instance.data('mage-multiselect2').loadOptions).toHaveBeenCalledWith(1);
        });
    });
});

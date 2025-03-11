/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'underscore',
    'uiElement',
    'Magento_Ui/js/grid/export'
], function ($, _, Element, ExportComponent) {
    'use strict';

    describe('Magento_Ui/js/grid/export', function () {
        var obj;

        beforeEach(function () {
            // Mock for selections
            var selectionsMock = {
                getSelections: function () {
                    return {
                        excludeMode: false,
                        params: {
                            filters: 'filters',
                            search: 'search',
                            namespace: 'namespace'
                        },
                        selected: [1, 2, 3]
                    };
                }
            };

            // Initialize the instance with the mock
            obj = new ExportComponent({
                ns: 'testNamespace',  // Define ns here
                options: [
                    {value: 'csv', url: '/export/csv'},
                    {value: 'xml', url: '/export/xml'}
                ],
                additionalParams: {foo: 'bar'},
                imports: {},  // Ensure imports is initialized
                selections: selectionsMock // Pass the mock
            });

            // Manually set the selections observable to the mock
            obj.selections(selectionsMock);

            // Mocking $.ajax to avoid actual server calls
            spyOn($, 'ajax').and.callFake(function (options) {
                if (options.url === '/export/csv') {
                    options.success('csvData');
                } else if (options.url === '/export/xml') {
                    options.success('xmlData');
                }
            });
        });

        it('should initialize correctly', function () {
            expect(obj.template).toEqual('ui/grid/exportButton');
            expect(obj.selectProvider).toEqual('ns = testNamespace, index = ids');
        });

        it('should initialize checked value', function () {
            obj.initChecked();
            expect(obj.checked()).toEqual('csv');
        });

        it('should observe checked property', function () {
            spyOn(obj, 'observe').and.callThrough();
            obj.initObservable();
            expect(obj.observe).toHaveBeenCalledWith('checked');
        });

        it('should return active option', function () {
            obj.checked('xml');
            let option = obj.getActiveOption();

            expect(option.value).toEqual('xml');
        });

        it('should apply the option and call postRequest', function () {
            spyOn(obj, 'getActiveOption').and.returnValue({ url: 'test-url' });
            spyOn(obj, 'postRequest');
            obj.applyOption();
            expect(obj.postRequest).toHaveBeenCalledWith({ url: 'test-url' });
        });

        it('should post request correctly', function () {
            spyOn(obj, 'getParams').and.returnValue({ param1: 'value1' });

            obj.postRequest({ url: 'test-url' });

            expect($.ajax).toHaveBeenCalledWith(jasmine.objectContaining({
                url: 'test-url',
                type: 'POST',
                data: $.param({ param1: 'value1' }),
                showLoader: true
            }));
        });
    });
});

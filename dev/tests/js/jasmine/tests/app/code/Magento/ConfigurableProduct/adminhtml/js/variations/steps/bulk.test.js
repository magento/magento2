/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_ConfigurableProduct/js/variations/steps/bulk',
    'jquery',
    'ko',
    'underscore',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Catalog/js/product-gallery',
    'jquery/uppy-core',
    'mage/translate'
], function (Bulk, $) {
    'use strict';

    describe('Magento_ConfigurableProduct/js/variations/steps/bulk', function () {
        let bulkInstance, config;

        beforeEach(function () {
            config = {
                isResizeEnabled: true,
                maxWidth: 800,
                maxHeight: 600
            };
            bulkInstance = new Bulk(config);

            window.FORM_KEY = 'mocked_form_key';
        });

        describe('bindGalleries', function () {
            beforeEach(function () {
                spyOn($.fn, 'mage').and.returnValue({
                    productGallery: jasmine.createSpy('productGallery')
                });
                spyOn($.fn, 'trigger');
                spyOn($.fn, 'data').and.returnValue(false);
                spyOn($.fn, 'find').and.returnValue($('<div></div>'));
                spyOn($.fn, 'on');
                spyOn($.fn, 'each').and.callFake(function (callback) {
                    callback.call(this, 0, $('<div></div>'));
                });

                window.Uppy = {
                    Uppy: jasmine.createSpy('Uppy').and.returnValue({
                        use: jasmine.createSpy('use'),
                        on: jasmine.createSpy('on')
                    }),
                    Dashboard: jasmine.createSpy('Dashboard'),
                    Compressor: jasmine.createSpy('Compressor'),
                    DropTarget: jasmine.createSpy('DropTarget'),
                    XHRUpload: jasmine.createSpy('XHRUpload')
                };
            });

            it('should initialize galleries and Uppy', function () {
                bulkInstance.bindGalleries();

                expect($.fn.mage).toHaveBeenCalledWith('productGallery', jasmine.any(Object));
                expect(window.Uppy.Uppy).toHaveBeenCalled();
                expect(window.Uppy.Uppy().use).toHaveBeenCalledWith(window.Uppy.Dashboard, jasmine.any(Object));
                expect(window.Uppy.Uppy().use).toHaveBeenCalledWith(window.Uppy.Compressor, jasmine.any(Object));
                expect(window.Uppy.Uppy().use).toHaveBeenCalledWith(window.Uppy.DropTarget, jasmine.any(Object));
                expect(window.Uppy.Uppy().use).toHaveBeenCalledWith(window.Uppy.XHRUpload, jasmine.any(Object));
            });
        });
    });
});

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'Magento_Backend/js/media-uploader'
], function ($) {
    'use strict';

    describe('Magento_Backend/js/media-uploader::_create()', function () {

        beforeEach(function () {
            window.Uppy = {
                Uppy: jasmine.createSpy('Uppy'),
                Dashboard: jasmine.createSpy('Dashboard'),
                Compressor: jasmine.createSpy('Compressor'),
                DropTarget: jasmine.createSpy('DropTarget'),
                XHRUpload: jasmine.createSpy('XHRUpload')
            };

            window.FORM_KEY = 'form_key';

            window.Uppy.Uppy.and.returnValue({
                use: jasmine.createSpy('uppyUse'),
                on: jasmine.createSpy('uppyOn')
            });

            window.byteConvert = jasmine.createSpy('byteConvert');

            spyOn($.fn, 'appendTo');

            $('<div>').mediaUploader();
        });

        it('Uppy instance should get created with correct options', function () {
            expect(window.Uppy.Uppy).toHaveBeenCalledWith({
                autoProceed: true,
                onBeforeFileAdded: jasmine.any(Function),
                meta: {
                    'form_key': jasmine.any(String),
                    isAjax: true
                }
            });
        });

        it('Uppy should get configured with necessary plugins', function () {
            const uppyInstance = window.Uppy.Uppy.calls.mostRecent().returnValue;

            expect(uppyInstance.use).toHaveBeenCalledWith(window.Uppy.Dashboard, jasmine.any(Object));
            expect(uppyInstance.use).toHaveBeenCalledWith(window.Uppy.DropTarget, jasmine.any(Object));
            expect(uppyInstance.use).toHaveBeenCalledWith(window.Uppy.XHRUpload, jasmine.any(Object));
        });
    });

    describe('Magento_Backend/js/media-uploader::_create() with resize enabled', function () {
        let onBeforeFileAdded, compressorOptions;

        beforeEach(function () {
            window.Uppy = {
                Uppy: jasmine.createSpy('Uppy'),
                Dashboard: jasmine.createSpy('Dashboard'),
                Compressor: jasmine.createSpy('Compressor'),
                DropTarget: jasmine.createSpy('DropTarget'),
                XHRUpload: jasmine.createSpy('XHRUpload')
            };

            window.FORM_KEY = 'form_key';

            window.Uppy.Uppy.and.returnValue({
                use: jasmine.createSpy('uppyUse'),
                on: jasmine.createSpy('uppyOn')
            });

            window.byteConvert = jasmine.createSpy('byteConvert');

            spyOn($.fn, 'appendTo');

            $('<div>').mediaUploader({
                isResizeEnabled: true,
                maxWidth: 1920,
                maxHeight: 1200
            });

            onBeforeFileAdded = window.Uppy.Uppy.calls.mostRecent().args[0].onBeforeFileAdded;

            const uppyInstance = window.Uppy.Uppy.calls.mostRecent().returnValue,
                compressorCall = uppyInstance.use.calls.all().find(function (call) {
                    return call.args[0] === window.Uppy.Compressor;
                });

            compressorOptions = compressorCall.args[1];
        });

        it('should abort compression for an animated GIF file', function () {
            onBeforeFileAdded({
                name: 'animated.gif',
                extension: 'gif',
                size: 1024,
                id: 'file-1'
            });

            const abort = jasmine.createSpy('abort');

            compressorOptions.beforeDraw.call({
                abort: abort
            });

            expect(abort).toHaveBeenCalled();
        });

        it('should not abort compression for an allowed non-GIF file', function () {
            onBeforeFileAdded({
                name: 'photo.jpg',
                extension: 'jpg',
                size: 1024,
                id: 'file-2'
            });

            const abort = jasmine.createSpy('abort');

            compressorOptions.beforeDraw.call({
                abort: abort
            });

            expect(abort).not.toHaveBeenCalled();
        });
    });
});

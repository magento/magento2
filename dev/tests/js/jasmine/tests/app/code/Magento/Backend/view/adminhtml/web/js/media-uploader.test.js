/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
});

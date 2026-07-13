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

    describe('Magento_Backend/js/media-uploader::upload batch ordering', function () {
        var element,
            onBeforeFileAdded,
            handlers,
            addItemOrder;

        /**
         * @param {String} id
         * @param {String} name
         * @returns {Object}
         */
        function acceptFile(id, name) {
            return onBeforeFileAdded({
                id: id,
                name: name,
                extension: 'jpg'
            });
        }

        beforeEach(function () {
            handlers = {};

            window.Uppy = {
                Uppy: jasmine.createSpy('Uppy').and.callFake(function (config) {
                    onBeforeFileAdded = config.onBeforeFileAdded;

                    return {
                        use: jasmine.createSpy('uppyUse'),

                        /**
                         * @param {String} eventName
                         * @param {Function} handler
                         */
                        on: function (eventName, handler) {
                            handlers[eventName] = handler;
                        }
                    };
                }),
                Dashboard: jasmine.createSpy('Dashboard'),
                Compressor: jasmine.createSpy('Compressor'),
                DropTarget: jasmine.createSpy('DropTarget'),
                XHRUpload: jasmine.createSpy('XHRUpload')
            };

            window.FORM_KEY = 'form_key';
            window.byteConvert = jasmine.createSpy('byteConvert');

            spyOn($.fn, 'appendTo');

            element = $('<div>').mediaUploader();
            addItemOrder = [];
            element.on('addItem', function (event, imageData) {
                addItemOrder.push(imageData.name);
            });
        });

        it('inserts gallery items in the order files were selected, not response order', function () {
            var fileA = acceptFile('a', '1.jpg'),
                fileB = acceptFile('b', '2.jpg'),
                fileC = acceptFile('c', '3.jpg');

            // simulate the slowest upload (fileA) resolving last, as happens with
            // concurrent uploads whose server-side processing time varies
            handlers['upload-success'](fileC, { body: { name: fileC.name } });
            handlers['upload-success'](fileB, { body: { name: fileB.name } });
            handlers['upload-success'](fileA, { body: { name: fileA.name } });
            handlers['complete']();

            expect(addItemOrder).toEqual(['1.jpg', '2.jpg', '3.jpg']);
        });

        it('skips files that failed to upload but keeps the remaining order', function () {
            var fileA = acceptFile('a', '1.jpg'),
                fileB = acceptFile('b', '2.jpg'),
                fileC = acceptFile('c', '3.jpg');

            handlers['upload-success'](fileB, { body: { name: fileB.name } });
            handlers['upload-error'](null, fileA);
            handlers['upload-success'](fileC, { body: { name: fileC.name } });
            handlers['complete']();

            expect(addItemOrder).toEqual(['2.jpg', '3.jpg']);
        });

        it('starts a fresh queue for the next batch after completion', function () {
            var first = acceptFile('a', '1.jpg');

            handlers['upload-success'](first, { body: { name: first.name } });
            handlers['complete']();

            var second = acceptFile('b', '2.jpg');

            handlers['upload-success'](second, { body: { name: second.name } });
            handlers['complete']();

            expect(addItemOrder).toEqual(['1.jpg', '2.jpg']);
        });
    });
});

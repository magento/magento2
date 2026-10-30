/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_ProductVideo/js/new-video-dialog'
], function ($) {
    'use strict';

    describe('Magento_ProductVideo/js/new-video-dialog', function () {
        var dialog,
            container,
            previewRequests,
            cancelled;

        beforeEach(function () {
            previewRequests = [];
            cancelled = 0;

            spyOn($, 'ajax').and.callFake(function (options) {
                previewRequests.push(options);
            });

            container = $(
                '<div>' +
                '<form id="new_video_form">' +
                '<input type="hidden" id="item_id" value=""/>' +
                '<div id="new_video_screenshot_preview"></div>' +
                '</form>' +
                '</div>'
            ).appendTo(document.body);

            dialog = Object.create($.mage.newVideoDialog.prototype);
            dialog.options = {
                saveRemoteVideoUrl: '/admin/product_video/product_gallery/retrieveImage/'
            };
            dialog.element = container;
            dialog._gallery = $('<div></div>');
            dialog._videoUrlWidget = $('<input type="text"/>');
            dialog._videoUrlWidget.on('cancelled_video_information', function () {
                cancelled++;
            });
            dialog._previewRequestId = 0;
            dialog._previewImage = null;
            dialog._tempPreviewImageData = null;

            spyOn(dialog, '_blockActionButtons');
        });

        afterEach(function () {
            container.remove();
            container = null;
            dialog = null;
        });

        it('Keeps the latest remote preview when an older one responds later', function () {
            dialog._loadRemotePreview('https://i.ytimg.com/vi/a/hqdefault.jpg');
            dialog._loadRemotePreview('https://i.ytimg.com/vi/b/hqdefault.jpg');

            previewRequests[1].success({
                file: 'b.jpg'
            });
            previewRequests[0].success({
                file: 'a.jpg'
            });

            expect(dialog._tempPreviewImageData).toEqual({
                file: 'b.jpg'
            });
            expect(dialog._getPreviewImage().attr('src')).toBe('https://i.ytimg.com/vi/b/hqdefault.jpg');
            expect(dialog._blockActionButtons.calls.allArgs().filter(function (args) {
                return args[0] === false;
            }).length).toBe(1);
        });

        it('Discards a remote preview that responds after the dialog was closed', function () {
            dialog._loadRemotePreview('https://i.ytimg.com/vi/a/hqdefault.jpg');
            dialog._onClose();

            previewRequests[0].success({
                file: 'a.jpg'
            });

            expect(cancelled).toBe(1);
            expect(dialog._tempPreviewImageData).toBeNull();
            expect(dialog._previewImage).toBeNull();
            expect(dialog._blockActionButtons).not.toHaveBeenCalledWith(false, true);
        });
    });
});

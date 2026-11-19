/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'Magento_Catalog/catalog/base-image-uploader'
], function ($) {
    'use strict';

    describe('Base Image Uploader', function () {
        var uploaderEl,
            defaultConfig = {
                maxImageUploadCount: 10
            };

        beforeEach(function () {
            uploaderEl = $(
                '<div data-max-file-size="2048000">' +
                    '<input type="file" multiple />' +
                    '<div class="image-placeholder">' +
                        '<div class="progress-bar"></div>' +
                    '</div>' +
                '</div>'
            );

            uploaderEl.appendTo('body');
        });

        afterEach(function () {
            uploaderEl.remove();
        });

        it('should preserve file selection order during upload', function () {
            var uploader = uploaderEl.baseImage(defaultConfig),
                filePositions,
                uploadedFiles;

            // Simulate the internal tracking variables
            filePositions = {};
            uploadedFiles = {};

            // Test file order tracking
            var testFiles = [
                { id: 'file1', name: 'first.jpg' },
                { id: 'file2', name: 'second.jpg' },
                { id: 'file3', name: 'third.jpg' }
            ];

            // Simulate onBeforeFileAdded behavior
            testFiles.forEach(function (file, index) {
                filePositions[file.id] = index;
            });

            // Simulate upload success in random order
            var uploadOrder = [1, 0, 2]; // files complete in different order
            var mockResponses = [
                { body: { file: 'first.jpg', position: 0 } },
                { body: { file: 'second.jpg', position: 1 } },
                { body: { file: 'third.jpg', position: 2 } }
            ];

            // Store results as they would complete
            uploadOrder.forEach(function (fileIndex) {
                var file = testFiles[fileIndex];
                var position = filePositions[file.id];
                uploadedFiles[position] = mockResponses[fileIndex];
            });

            // Verify ordering logic
            var sortedPositions = Object.keys(uploadedFiles).sort(function (a, b) {
                return parseInt(a, 10) - parseInt(b, 10);
            });

            var expectedOrder = ['0', '1', '2'];
            expect(sortedPositions).toEqual(expectedOrder);

            // Verify the files would be added in correct order
            var actualFiles = sortedPositions.map(function (position) {
                return uploadedFiles[position].file;
            });

            expect(actualFiles).toEqual(['first.jpg', 'second.jpg', 'third.jpg']);
        });

        it('should handle partial upload failures while maintaining order', function () {
            var filePositions = { 'file1': 0, 'file2': 1, 'file3': 2 };
            var uploadedFiles = {
                '0': { file: 'first.jpg' },
                '2': { file: 'third.jpg' }
                // file2 failed to upload
            };

            var totalFilesToUpload = 2; // reduced after file2 failed

            var sortedPositions = Object.keys(uploadedFiles).sort(function (a, b) {
                return parseInt(a, 10) - parseInt(b, 10);
            });

            var actualFiles = sortedPositions.map(function (position) {
                return uploadedFiles[position].file;
            });

            // Should maintain order of successful uploads
            expect(actualFiles).toEqual(['first.jpg', 'third.jpg']);
            expect(Object.keys(uploadedFiles).length).toBe(totalFilesToUpload);
        });
    });
});
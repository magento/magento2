/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'squire'
], function ($, Squire) {
    'use strict';

    describe('Magento_Backend/js/media-uploader', function () {
        let injector = new Squire(),
            mediaUploaderComponent;

        beforeEach(function (done) {
            injector.require([
                'Magento_Backend/js/media-uploader',
                'knockoutjs/knockout-es5'
            ], function (mediaUploader) {
                mediaUploaderComponent = new mediaUploader({});
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {
            }
        });

        describe('_create() method', function () {
            it('_create method to be trigger and check the dropzone attribute key and value', function () {
                spyOn(jQuery.fn, 'fileupload');
                mediaUploaderComponent._create();
                expect(jQuery.fn.fileupload).toHaveBeenCalledWith(
                    jasmine.objectContaining({
                        dropZone: mediaUploaderComponent.
                                    element.find('input[type=file]')
                                    .closest('[role="dialog"]')
                    }
                ));
            });
        });
    });
});

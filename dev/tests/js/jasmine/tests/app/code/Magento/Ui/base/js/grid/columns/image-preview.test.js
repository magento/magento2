/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks, no-undef */

define([
    'Magento_Ui/js/grid/columns/image-preview',
    'ko',
    'jquery'
], function (Preview, ko, $) {
    'use strict';

    describe('Ui/js/grid/columns/image-preview', function () {
        var record = {
            _rowIndex: 1,
            rowNumber: 1
        },
           imagePreview;

        beforeEach(function () {
            imagePreview = new Preview();

            /**
             * @return {Object}
             */
            function getThumbnail()  {
                return {
                    previewRowId: ko.observable()
                };
            }

            imagePreview.thumbnailComponent = getThumbnail;

            imagePreview.visibleRecord = ko.observable(1);
        });

        describe('show method', function () {
            it('show image', function () {
                var mockImg = document.createElement('img'),
                    hide = spyOn(imagePreview, 'hide');

                spyOn($.fn, 'get').and.returnValue(mockImg);
                imagePreview.show(record);
                expect(hide).toHaveBeenCalledTimes(1);
            });

        });
    });
});

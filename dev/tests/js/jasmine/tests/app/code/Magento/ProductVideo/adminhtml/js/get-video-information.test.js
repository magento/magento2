/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_ProductVideo/js/get-video-information'
], function ($) {
    'use strict';

    describe('Testing Youtube player Widget', function () {
        var wdContainer;

        beforeEach(function () {
            wdContainer = $(
                '<div>' +
                '<div class="video-information uploader"><span></span></div>' +
                '<div class="video-player-container">' +
                '<div class="product-video"></div>' +
                '</div>' +
                '</div>');
        });

        afterEach(function () {
            $(wdContainer).remove();
        });

        it('Widget does not stops player if player is no defined', function () {
            var video = wdContainer.find('.video-player-container').find('.product-video'),
                widget;

            video.videoYoutube();
            widget = video.data('mageVideoYoutube');
            widget.stop = jasmine.createSpy();
            widget._player = {
                destroy: jasmine.createSpy()
            };
            widget.destroy();
            expect(widget._player).toBeUndefined();
            widget.destroy();
            expect(widget.stop).toHaveBeenCalledTimes(1);
        });
    });
});

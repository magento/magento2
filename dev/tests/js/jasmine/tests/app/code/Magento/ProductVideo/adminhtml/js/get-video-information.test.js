/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable */
define([
    'jquery',
    'Magento_ProductVideo/js/get-video-information'
], function ($) {
    'use strict';

    describe('Testing Youtube player Widget', function () {
        var wdContainer, video, widget;

        beforeAll(function () {
            // Global mocks to prevent browser-native errors
            spyOn(window, 'open').and.callFake(() => ({
                //eslint-disable-line max-nested-callbacks
                focus: function () {}
            }));

            if (typeof navigator !== 'undefined' && !navigator.share) {
                Object.defineProperty(navigator, 'share', {
                    value: () => Promise.resolve(),
                    writable: true
                });
            }
        });

        beforeEach(function () {
            // Create DOM structure for widget
            wdContainer = $(
                '<div>' +
                '<div class="video-information uploader"><span></span></div>' +
                '<div class="video-player-container">' +
                '<div class="product-video"></div>' +
                '</div>' +
                '</div>'
            ).appendTo(document.body);

            video = wdContainer.find('.product-video');
            video.videoYoutube();
            widget = video.data('mageVideoYoutube');

            // Set spies
            widget.stop = jasmine.createSpy();
            widget._player = {
                destroy: jasmine.createSpy()
            };
        });

        afterEach(function () {
            // Properly destroy widget
            if (widget && typeof widget.destroy === 'function') {
                widget.destroy();
            }

            // Remove leaked iframes
            document.querySelectorAll('iframe').forEach((iframe) => iframe.remove());

            // Clean up global YouTube API objects and scripts
            if (window.YT && window.YT.Player) {
                delete window.YT;
            }
            if (window.onYouTubeIframeAPIReady) {
                delete window.onYouTubeIframeAPIReady;
            }
            document.querySelectorAll('script[src*="youtube.com"]').forEach((s) => s.remove());

            // Clean up DOM and variables
            wdContainer.remove();
            wdContainer = null;
            video = null;
            widget = null;
        });

        it('Widget does not stop player if player is not defined', function () {
            widget.destroy(); // First destroy call - will clean _player
            expect(widget._player).toBeUndefined();

            widget.destroy(); // Second call - should trigger stop
            expect(widget.stop).toHaveBeenCalledTimes(1);
        });
    });
});

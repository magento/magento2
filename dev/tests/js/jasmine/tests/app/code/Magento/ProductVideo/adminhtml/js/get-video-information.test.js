/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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

    describe('Testing videoData widget request lifecycle', function () {
        var input,
            widget,
            requests,
            updated,
            errors,
            youtubeA = 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
            youtubeB = 'https://www.youtube.com/watch?v=bbbbbbbbbbb',
            vimeoUrl = 'https://vimeo.com/123456';

        /**
         * @param {String} id
         * @returns {Object}
         */
        function youtubeResponse(id) {
            return {
                items: [{
                    snippet: {
                        publishedAt: '2020-01-01T00:00:00.000Z',
                        channelTitle: 'channel',
                        channelId: 'channelId',
                        localized: {
                            title: 'title ' + id
                        },
                        description: 'description',
                        thumbnails: {
                            high: {
                                url: 'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg'
                            }
                        }
                    },
                    contentDetails: {
                        duration: 'PT1M'
                    }
                }]
            };
        }

        /**
         * @param {String} url
         */
        function requestInformation(url) {
            input.val(url);
            input.trigger('update_video_information');
        }

        beforeEach(function () {
            requests = [];
            updated = [];
            errors = [];

            spyOn($.mage.videoData.prototype, '_onVideoInvalid');
            spyOn($, 'getJSON').and.callFake(function (url, data, success) {
                var request = {
                    url: url,
                    success: success,
                    failure: null
                };

                requests.push(request);

                return {
                    /**
                     * @param {Function} callback
                     */
                    fail: function (callback) {
                        request.failure = callback;
                    }
                };
            });
            spyOn($, 'ajax').and.callFake(function (options) {
                requests.push({
                    url: options.url,
                    success: options.success,
                    failure: options.error
                });
            });

            input = $('<input type="text" name="video_url"/>').appendTo(document.body);
            input.videoData({
                youtubeKey: 'key'
            });
            widget = input.data('mageVideoData');

            input.on('updated_video_information', function (event, data) {
                updated.push(data);
            });
            input.on('error_updated_information', function (event, error) {
                errors.push(error);
            });
        });

        afterEach(function () {
            input.remove();
            input = null;
            widget = null;
        });

        it('Ignores a response that arrives after the request was cancelled', function () {
            requestInformation(youtubeA);
            input.trigger('cancelled_video_information');
            input.val('');

            requests[0].success(youtubeResponse('aaaaaaaaaaa'));

            expect(updated.length).toBe(0);
            expect(errors.length).toBe(0);
        });

        it('Ignores a response that arrives after the url was cleared', function () {
            requestInformation(vimeoUrl);
            requestInformation('');

            requests[0].success({
                title: 'title',
                description: 'description',
                duration: 60,
                'author_name': 'author',
                'author_url': 'https://vimeo.com/author',
                'upload_date': '2020-01-01',
                'thumbnail_url': 'https://i.vimeocdn.com/video/1.jpg'
            });

            expect(updated.length).toBe(0);
        });

        it('Emits only the latest request when responses arrive out of order', function () {
            requestInformation(youtubeA);
            requestInformation(youtubeB);

            requests[1].success(youtubeResponse('bbbbbbbbbbb'));
            requests[0].success(youtubeResponse('aaaaaaaaaaa'));

            expect(updated.length).toBe(1);
            expect(updated[0].videoId).toBe('bbbbbbbbbbb');
            expect(widget._videoInformation.videoId).toBe('bbbbbbbbbbb');
        });

        it('Emits the latest request when an older response arrives first', function () {
            requestInformation(youtubeA);
            requestInformation(youtubeB);

            requests[0].success(youtubeResponse('aaaaaaaaaaa'));
            requests[1].success(youtubeResponse('bbbbbbbbbbb'));

            expect(updated.length).toBe(1);
            expect(updated[0].videoId).toBe('bbbbbbbbbbb');
        });

        it('Ignores a failure of an outdated request', function () {
            requestInformation(youtubeA);
            requestInformation(youtubeB);

            requests[0].failure();

            expect(errors.length).toBe(0);
            expect(widget._currentVideoUrl).toBe(youtubeB);

            requests[1].success(youtubeResponse('bbbbbbbbbbb'));

            expect(updated.length).toBe(1);
            expect(updated[0].videoId).toBe('bbbbbbbbbbb');
        });

        it('Ignores a failure of a cancelled vimeo request', function () {
            requestInformation(vimeoUrl);
            input.trigger('cancelled_video_information');

            requests[0].failure();

            expect(errors.length).toBe(0);
        });

        it('Requests the same url again after cancellation', function () {
            requestInformation(youtubeA);
            input.trigger('cancelled_video_information');
            requestInformation(youtubeA);

            expect(requests.length).toBe(2);

            requests[0].success(youtubeResponse('aaaaaaaaaaa'));
            expect(updated.length).toBe(0);

            requests[1].success(youtubeResponse('aaaaaaaaaaa'));
            expect(updated.length).toBe(1);
        });

        it('Does not repeat a pending request for the same url', function () {
            requestInformation(youtubeA);
            requestInformation(youtubeA);

            expect(requests.length).toBe(1);

            requests[0].success(youtubeResponse('aaaaaaaaaaa'));
            expect(updated.length).toBe(1);
        });
    });
});

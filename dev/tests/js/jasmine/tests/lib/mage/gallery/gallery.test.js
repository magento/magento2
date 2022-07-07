/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'mage/gallery/gallery',
    'jquery'
], function (Gallery, $) {
    'use strict';

    var gallery,
        options,
        element,
        jqueryDataMock,
        originSpy;

    beforeEach(function () {
        options = {
            options: {
                allowfullscreen: true,
                arrows: true,
                height: 700,
                keyboard: true,
                loop: true,
                nav: 'thumbs',
                navarrows: true,
                navdir: 'horizontal',
                navtype: 'slides',
                showCaption: false,
                thumbheight: 110,
                thumbwidth: 88,
                transition: 'slide',
                transitionduration: 500,
                width: 700
            },
            fullscreen: {
                arrows: true,
                loop: true,
                nav: 'thumbs',
                navarrows: false,
                navdir: 'horizontal',
                navtype: 'slides',
                showCaption: false,
                transition: 'slide',
                transitionduration: 500
            },
            breakpoints: {
                mobile: {}
            },
            data: [
                {
                    caption: 'Simple product',
                    isMain: true,
                    position: '1',
                    type: 'image',
                    videoUrl: null,
                    thumb: '',
                    full: '',
                    img: ''
                }
            ],
            magnifierOpts: {
                enabled: false,
                eventType: 'hover',
                fullscreenzoom: '20',
                height: 100,
                largeWrapper: '[data-gallery-role=\'magnifier\']',
                left: 10,
                mode: 'outside',
                thumb: '.fotorama__img',
                top: 10,
                width: 100,
                zoomable: false
            }
        };
        element = $('<div class="gallery-placeholder' +
            ' _block-content-loading" data-gallery-role="gallery-placeholder">' +
            '<img alt="main product photo" class="gallery-placeholder__image" src="">' +
            '</div>');
    });

    describe('"initGallery" method', function () {
        it('Verify gallery initialization', function () {
            originSpy = $.fn.data;
            jqueryDataMock = {
                setOptions: jasmine.createSpy().and.returnValue(true),
                updateOptions: jasmine.createSpy().and.returnValue(true)
            };
            spyOn($.fn, 'data').and.callFake(function () {
                return jqueryDataMock;
            });

            gallery = new Gallery(options, element);
            expect(gallery.settings.$elementF.class).toBe(element[1]);
            expect(gallery.settings.fullscreenConfig).toBeDefined();
            expect(gallery.settings.fotoramaApi).toBeDefined();
            expect(gallery.settings.data).toBeDefined();
            expect(gallery.settings.api).toBeDefined();
            expect(gallery.settings.activeBreakpoint).toEqual({});

            $.fn.data = originSpy;
        });

        it('Verify gallery navigation is set properly as dots if specified in options', function () {
            // added
            options.breakpoints = {
                mobile: {
                    conditions: {
                        'max-width': '767px'
                    },
                    options: {
                        options: {
                            nav: 'dots'
                        }
                    }
                },
                desktop: {
                    conditions: {
                        'min-width': '1024px'
                    },
                    options: {
                        options: {
                            nav: 'thumbs'
                        }
                    }
                }
            };

            originSpy = $.fn.data;
            jqueryDataMock = {
                setOptions: jasmine.createSpy().and.returnValue(true),
                updateOptions: jasmine.createSpy().and.returnValue(true)
            };
            spyOn($.fn, 'data').and.callFake(function () {
                return jqueryDataMock;
            });

            gallery = new Gallery(options, element);

            options.breakpoints.mobile.options.options.arrows = false;
            expect(JSON.stringify(gallery.settings.activeBreakpoint))
              .toEqual(JSON.stringify(options.breakpoints.mobile.options));

            $.fn.data = originSpy;
        });

    });
});

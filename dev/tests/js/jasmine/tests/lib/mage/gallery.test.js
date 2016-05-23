/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'text!tests/assets/gallery/config.json',
    'mage/gallery/gallery',
    'magnifier/magnify'
], function ($, config, gallery, magnifier) {

    'use strict';

    var body = $('body'),
        galleryAPI,
        conf = JSON.parse(config),
        gallerySelector = '[data-gallery-role="gallery"]',
        magnifierSelector = '[data-gallery-role="magnifier"]',
        stageSelector = '[data-gallery-role="stage-shaft"]',
        navSelector = '[data-gallery-role="nav-frame"]',
        dotSelector = '[data-nav-type="dot"]',
        navWrap = '[data-gallery-role="nav-wrap"]',
        dataToUpdate = [
            {
                img: 'data:image/png;base64,' +
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP4Xw8AAoABf5/NhYYAAAAASUVORK5CYII='
            }, {
                img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAw' +
                'CAAAAC0lEQVR42mP4Xw8AAoABf5/NhYYAAAAASUVORK5CYII='
            }, {
                img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAw' +
                'CAAAAC0lEQVR42mP4Xw8AAoABf5/NhYYAAAAASUVORK5CYII='
            }, {
                img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAw' +
                'CAAAAC0lEQVR42mP4Xw8AAoABf5/NhYYAAAAASUVORK5CYII='
            }
        ],
        waitsFor = function (test, fn) {
            if (test()) {
                fn();
            } else {
                setTimeout(function () {
                    waitsFor(test, fn);
                }, 10);
            }
        };

    gallery(magnifier(conf, body), body);

    beforeEach(function () {
        galleryAPI = $(gallerySelector).data('gallery');
    });

    describe('magnifier/magnify', function () {

        if ('ontouchstart' in document.documentElement) {
            it('magnifier is not initialized on mobile platforms', function () {
                expect($(magnifierSelector).length).toBe(0);
            });
        } else {
            it('magnifier is defined', function () {
                expect($(gallerySelector).magnify).toBeDefined();
                expect(typeof $(gallerySelector).magnify).toBe('function');
                expect($(magnifierSelector).hasClass('hidden')).toBeTruthy();
            });

            it('magnifier is initialized on desktop platforms', function () {
                expect($(magnifierSelector + ' img').attr('src')).toBe($(stageSelector + ' img').attr('src'));
            });
            it('magnifier appearing on event on desktop platforms', function () {
                var ev = conf.magnifierOpts.eventType === 'click' ? 'click' : 'mouseover';
                expect($($(magnifierSelector).children()[0]).hasClass('magnifier-large hidden')).toBeTruthy();
                $(stageSelector + ' img').trigger(ev);
                expect($($(magnifierSelector).children()[0]).hasClass('magnifier-large hidden')).toBeFalsy();
                $(stageSelector + ' img').trigger('mouseleave');
            });
        }
    });

    describe('mage/gallery/gallery', function () {

        it('gallery loaded', function () {
            expect($(navSelector).length).toBe(conf.data.length);
        });

        it('show last', function () {
            galleryAPI.last();
            expect($(navSelector + ':eq(' + (conf.data.length - 1) + ')')
                .attr('data-active') === 'true').toBeTruthy();
        });

        it('show first', function () {
            galleryAPI.first();
            expect($(navSelector + ':eq(0)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show next', function () {
            galleryAPI.next();
            expect($(navSelector + ':eq(1)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show previos', function () {
            galleryAPI.prev();
            expect($(navSelector + ':eq(0)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show by number', function () {
            galleryAPI.seek(3);
            expect($(navSelector + ':eq(2)').attr('data-active') === 'true').toBeTruthy();
        });

        it('update options', function () {
            expect($(navSelector).attr('data-nav-type') === 'thumb').toBeTruthy();
            galleryAPI.updateOptions({
                nav: 'dots'
            });
            expect($(dotSelector).length).toBe(conf.data.length);
        });

        it('update data', function () {
            galleryAPI.updateData(dataToUpdate);
            expect($(dotSelector).length).toBe(dataToUpdate.length);
        });

        it('breakpoints override configs', function () {
            expect($('.fotorama__arr').css('display')).toBe('block');
        });

        it('fullscreen enter', function (done) {
            expect($(navWrap).css('display') === 'block').toBeTruthy();
            galleryAPI.fotorama.requestFullScreen();

            waitsFor(function () {
                return $(navWrap).css('display') !== 'block';
            }, function () {
                expect($(navWrap).css('display') === 'none').toBeTruthy();
                done();
            });
        });

        it('fullscreen exit', function (done) {
            expect($(navWrap).css('display') === 'none').toBeTruthy();
            galleryAPI.fotorama.cancelFullScreen();

            waitsFor(function () {
                return $(navWrap).css('display') !== 'none';
            }, function () {
                expect($(navWrap).css('display') === 'block').toBeTruthy();
                done();
            });
        });

    });
});

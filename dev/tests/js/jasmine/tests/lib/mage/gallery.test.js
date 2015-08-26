/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'text!tests/assets/gallery/config.json',
    'mage/gallery/gallery',
    'magnifier/magnify'
], function ($, config, gallery, magnifier) {

    'use strict';

    var body = $('body');
    var galleryAPI;

    var conf =  JSON.parse(config);

    var dataToUpdate = [
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
    ];

    gallery(magnifier(conf, body), body);

    beforeEach(function () {
        galleryAPI = $("[data-gallery-role='gallery']").data('gallery');
    });

    describe('gallery magnifier', function () {

        it('magnifier is defined', function () {
            expect($("[data-gallery-role='gallery']").magnify).toBeDefined();
            expect(typeof $("[data-gallery-role='gallery']").magnify).toBe('function');
        });

        it('magnifier thumb config', function () {
            console.log($("[data-gallery-role='magnifier']").attr('class'));
            expect($("[data-gallery-role='magnifier'] img").attr('src')).toBe($("[data-gallery-role='stage-shaft'] img").attr('src'));
        });

        it('magnifier appearing', function () {
            var ev = conf.magnifierOpts.eventType === 'click' ? 'click':'mouseover';
            console.log($("[data-gallery-role='magnifier']").attr('class'));
            expect($($("[data-gallery-role='magnifier']").children()[0]).hasClass('hidden')).toBeTruthy();
            $("[data-gallery-role='stage-shaft'] img").trigger(ev);
            expect($($("[data-gallery-role='magnifier']").children()[0]).hasClass('hidden')).toBeFalthy;
            $("[data-gallery-role='stage-shaft'] img").trigger('mouseleave');
        });
    });

    describe('gallery API', function () {

        it('gallery loaded', function () {
            expect($("[data-gallery-role='nav-frame']").length).toBe(conf.data.length);
        });

        it('show last', function () {
            galleryAPI.last();
            expect($('[data-gallery-role="nav-frame"]:eq('+ (conf.data.length-1) +')')
                .attr('data-active') === 'true').toBeTruthy();
        });

        it('show first', function () {
            galleryAPI.first();
            expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show next', function () {
            galleryAPI.next();
            expect($('[data-gallery-role="nav-frame"]:eq(1)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show previos', function () {
            galleryAPI.prev();
            expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
        });

        it('show by number', function () {
            galleryAPI.seek(3);
            expect($('[data-gallery-role="nav-frame"]:eq(2)').attr('data-active') === 'true').toBeTruthy();
        });

        it('update options', function () {
            expect($('[data-gallery-role="nav-frame"]').attr('data-nav-type') === 'thumb').toBeTruthy();
            galleryAPI.updateOptions({
                nav: "dots"
            });
            expect($("[data-nav-type='dot']").length).toBe(conf.data.length);            
        });

        it('update data', function () {
            galleryAPI.updateData(dataToUpdate);
            expect($("[data-nav-type='dot']").length).toBe(dataToUpdate.length);
        });

        describe('gallery with breakpoints', function () {
            it('configuration stays default if breakpoint is not fired', function () {
                galleryAPI.last();
                galleryAPI.next();
                console.log($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active'))
                expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
                galleryAPI.updateOptions({
                    "breakpoints": {
                        "(max-width: 0px)": {
                            "loop": false
                        }
                    }
                });
                galleryAPI.last();
                galleryAPI.next();
                expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
            });

            it('configuration could be updated with breakpoints', function () {
                galleryAPI.last();
                galleryAPI.next();
                expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
                galleryAPI.updateOptions({
                    "breakpoints": {
                        "(min-width: 1px)": {
                            "loop": false
                        }
                    }
                });
                galleryAPI.last();
                galleryAPI.next();
                expect($('[data-gallery-role="nav-frame"]:eq(0)').attr('data-active') === 'true').toBeTruthy();
            });
        });
    //});
});


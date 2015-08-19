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
        galleryAPI = $('.fotorama-item').data('gallery');
    });

    describe('gallery magnifier', function () {

        it('magnifier is defined', function () {
            expect($('.fotorama-item').magnify).toBeDefined();
            expect(typeof $('.fotorama-item').magnify).toBe('function');
        });

        it('magnifier largeWrapper config', function () {
            expect('#' + $('.magnifier-large').parent().attr('id')).toBe(conf.magnifierOpts.largeWrapper);
        });

        it('magnifier thumb config', function () {
            expect($('.magnifier-large').attr('src')).toBe($('.fotorama__stage__frame .fotorama__img').attr('src'));
        });

        it('magnifier appearing', function () {
            var ev = conf.magnifierOpts.eventType === 'click' ? 'click':'mouseover';
            expect($($('.magnifier-preview').children()[0]).attr('class')).toBe('magnifier-large hidden');
            $('.fotorama__stage__frame .fotorama__img').trigger(ev);
            expect($($('.magnifier-preview').children()[0]).attr('class')).toBe('magnifier-large');
            $('.fotorama__stage__frame .fotorama__img').trigger('mouseleave');
        });
    });

    describe('gallery API', function () {

        it('gallery loaded', function () {
            expect($('.fotorama__nav__frame').length).toBe(conf.data.length);
        });

        it('show last', function () {
            galleryAPI.last();
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq('+ (conf.data.length-1) +')')
                .hasClass('fotorama__active')).toBeTruthy();
        });

        it('show first', function () {
            galleryAPI.first();
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(0)').hasClass('fotorama__active')).toBeTruthy();
        });

        it('show next', function () {
            galleryAPI.next();
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(1)').hasClass('fotorama__active')).toBeTruthy();
        });

        it('show previos', function () {
            galleryAPI.prev();
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(0)').hasClass('fotorama__active')).toBeTruthy();
        });

        it('show by number', function () {
            galleryAPI.seek(3);
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(2)').hasClass('fotorama__active')).toBeTruthy();
        });

        it('update options', function () {
            expect($('.fotorama__nav-wrap .fotorama__nav').hasClass('fotorama__nav--thumbs')).toBeTruthy();
            galleryAPI.updateOptions({
                nav: "dots"
            });
            expect($('.fotorama__nav-wrap .fotorama__nav').hasClass('fotorama__nav--dots')).toBeTruthy();
        });

        it('update data', function () {
            galleryAPI.updateData(dataToUpdate);
            expect($('.fotorama__nav__frame--dot').length).toBe(dataToUpdate.length);
        });

        describe('gallery with breakpoints', function () {
            it('configuration stays default if breakpoint is not fired', function () {
                galleryAPI.first();
                galleryAPI.prev();
                expect($('.fotorama__nav__shaft .fotorama__nav__frame--dot:eq(' + (dataToUpdate.length - 1) + ')').hasClass('fotorama__active')).toBeTruthy();
                galleryAPI.first();
                galleryAPI.updateOptions({
                    "breakpoints": {
                        "(max-width: 0px)": {
                            "loop": false
                        }
                    }
                });
                galleryAPI.prev();
                expect($('.fotorama__nav__shaft .fotorama__nav__frame--dot:eq(' + (dataToUpdate.length - 1) + ')').hasClass('fotorama__active')).toBeTruthy();
            });

            it('configuration could be updated with breakpoints', function () {
                galleryAPI.first();
                galleryAPI.prev();
                expect($('.fotorama__nav__shaft .fotorama__nav__frame--dot:eq(' + (dataToUpdate.length - 1) + ')').hasClass('fotorama__active')).toBeTruthy();
                galleryAPI.first();
                galleryAPI.updateOptions({
                    "breakpoints": {
                        "(min-width: 1px)": {
                            "loop": false
                        }
                    }
                });
                galleryAPI.prev();
                expect($('.fotorama__nav__shaft .fotorama__nav__frame--dot:eq(0)').hasClass('fotorama__active')).toBeTruthy();
            });
        });
    });
});


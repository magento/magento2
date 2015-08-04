/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'text!tests/assets/gallery/config.json',
    'mage/new-gallery'
], function ($, config, gallery) {
    'use strict';

    var body = $('body');
    var galleryAPI;

    var conf = JSON.parse(config);

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

    gallery(conf, body);

    beforeEach(function (done) {
        galleryAPI = $('.fotorama-item').data('gallery');
        done();
    });

    describe('mage/new-gallery', function () {
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
            galleryAPI.updateOptions({
                nav: "dots"
            });
            expect($('.fotorama__nav__shaft .fotorama__nav__frame').hasClass('fotorama__nav__frame--dot')).toBeTruthy();
        });

        it('update data', function () {
            galleryAPI.updateData(dataToUpdate);
            expect($('.fotorama__nav__frame').length).toBe(dataToUpdate.length);
        });
    });
});


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
            img: 'http://i.fotorama.io/ab52465c-b3d8-4bf6-986a-a4bf815dfaed/-/stretch/off/-/resize/1280x/'
        }, {
            img: 'http://i.fotorama.io/ab52465c-b3d8-4bf6-986a-a4bf815dfaed/-/stretch/off/-/resize/1280x/'
        }, {
            img: 'http://i.fotorama.io/ab52465c-b3d8-4bf6-986a-a4bf815dfaed/-/stretch/off/-/resize/1280x/'
        }, {
            img: 'http://i.fotorama.io/ab52465c-b3d8-4bf6-986a-a4bf815dfaed/-/stretch/off/-/resize/1280x/'
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
            galleryAPI.last();
        });

        it('show last', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq('+ (conf.data.length-1) +')').hasClass('fotorama__active')).toBeTruthy();
            galleryAPI.first();
        });

        it('show first', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(0)').hasClass('fotorama__active')).toBeTruthy();
            galleryAPI.next();
        });

        it('show next', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(1)').hasClass('fotorama__active')).toBeTruthy();
            galleryAPI.prev();
        });

        it('show previos', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(0)').hasClass('fotorama__active')).toBeTruthy();
            galleryAPI.seek(3);
        });

        it('show by number', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame:eq(2)').hasClass('fotorama__active')).toBeTruthy();
            galleryAPI.updateOptions({
                nav: "dots"
            });
        });

        it('update options', function () {
            expect($('.fotorama__nav__shaft .fotorama__nav__frame').hasClass('fotorama__nav__frame--dot')).toBeTruthy();
            galleryAPI.updateData(dataToUpdate);
        });

        it('update data', function () {
            expect($('.fotorama__nav__frame').length).toBe(dataToUpdate.length);
        });
    });
});


/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/**
 * Jasmine tests for magnifier.js
 */
describe('Magnifier', function () {
    'use strict';
    let $, configMagnify, $container, $thumb, $preview;

    beforeAll(function (done) {
        require(['/lib/web/magnifier/magnifier.js'], function () {
            $ = window.jQuery;
            configMagnify = $.fn.magnify;
            done();
        });
    });

    beforeEach(function () {
        $container = $('<div></div>');
        const dataUri = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

        $thumb = $('<img id="test-thumb" src="' + dataUri + '" />');
        $preview = $('<div class="magnifier-preview"></div>');
        $container.append($thumb);
        $('body').append($container).append($preview);
    });

    afterEach(function () {
        $container.remove();
        $preview.remove();
    });

    it('should not throw error if data is empty', function () {
        expect(function () {
            $container.magnify({ thumb: '#test-thumb', largeWrapper: '.magnifier-preview' });
        }).not.toThrow();
    });

    it('should show magnifier for valid image', function (done) {
        const configMagnifier = $container.magnify({thumb: '#test-thumb', largeWrapper: '.magnifier-preview'});

        setTimeout(function () {
            expect($('.magnifier-preview').length).toBe(1);
            done();
        }, 100);
    });

    it('should not break when navigating from PLP to PDP', function () {
        $container.magnify({ thumb: '#test-thumb', largeWrapper: '.magnifier-preview' });
        expect(function () {
            $container.magnify({ thumb: '#test-thumb', largeWrapper: '.magnifier-preview' });
        }).not.toThrow();
    });

    it('should not throw error if large image is missing', function (done) {
        $thumb.attr('src', '/404-image.jpg');
        expect(function () {
            $container.magnify({ thumb: '#test-thumb', largeWrapper: '.magnifier-preview' });
        }).not.toThrow();
        setTimeout(function () {
            expect($('.magnifier-preview').length).toBe(1);
            done();
        }, 100);
    });

    it('should not throw error if thumb image has zero width/height', function (done) {
        $thumb.css({ width: 0, height: 0 });
        $thumb.attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        expect(function () {
            $container.magnify({ thumb: '#test-thumb', largeWrapper: '.magnifier-preview' });
        }).not.toThrow();
        setTimeout(function () {
            expect($('.magnifier-preview').length).toBe(1);
            done();
        }, 100);
    });
});

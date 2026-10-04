/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
/**
 * Verifies fotorama stopEvent behavior on mobile vs desktop interactions.
 */
define([
    'jquery',
    'text!/lib/web/fotorama/fotorama.js'
], function ($, fotoramaSource) {
    'use strict';

    describe('lib/web/fotorama/fotorama.js stopEvent', function () {
        var originalMatchMedia, originalModernizr, stopEventFn;

        function extractStopEvent(source) {
            let start = source.indexOf('function stopEvent'),
                braceStart,
                depth,
                i,
                end,
                ch;

            if (start === -1) {
                return undefined;
            }
            braceStart = source.indexOf('{', start);
            depth = 0, i = braceStart, end = -1;
            for (; i < source.length; i++) {
                ch = source[i];
                if (ch === '{') {
                    depth++;
                } else if (ch === '}') {
                    depth--;
                    // eslint-disable-next-line max-depth
                    if (depth === 0) {
                        end = i + 1;
                        break;
                    }
                }
            }
            if (end === -1) {
                return undefined;
            }
            // eslint-disable-next-line no-new-func
            return new Function('return (' + source.slice(start, end) + ');')();
        }

        beforeAll(function () {
            originalMatchMedia = window.matchMedia;
            originalModernizr = window.Modernizr;
        });

        afterAll(function () {
            window.matchMedia = originalMatchMedia;
            window.Modernizr = originalModernizr;
        });

        beforeEach(function () {
            // Ensure Modernizr exists; the function references it
            window.Modernizr = window.Modernizr || { touch: false };
            stopEventFn = extractStopEvent(fotoramaSource);
        });

        function mockEvent(type) {
            return {
                type: type,
                preventDefault: jasmine.createSpy('preventDefault'),
                stopPropagation: jasmine.createSpy('stopPropagation'),
                returnValue: true
            };
        }

        it('prevents default and stops propagation on mobile touchend', function () {
            var e = mockEvent('touchend');

            window.matchMedia = function () { return { matches: true }; };
            stopEventFn(e);
            expect(e.preventDefault).toHaveBeenCalled();
            expect(e.stopPropagation).toHaveBeenCalled();
        });

        it('prevents default on desktop click when Modernizr.touch is false', function () {
            var e = mockEvent('click');

            window.matchMedia = function () { return { matches: false }; };
            window.Modernizr.touch = false;
            stopEventFn(e);
            expect(e.preventDefault).toHaveBeenCalled();
        });

        it('does not prevent default on desktop click when Modernizr.touch is true', function () {
            var e = mockEvent('click');

            window.matchMedia = function () { return { matches: false }; };
            window.Modernizr.touch = true;
            stopEventFn(e);
            expect(e.preventDefault).not.toHaveBeenCalled();
        });

        it('stops propagation when second argument is true', function () {
            var e = mockEvent('click');

            window.matchMedia = function () { return { matches: false }; };
            window.Modernizr.touch = false;
            stopEventFn(e, true);
            expect(e.stopPropagation).toHaveBeenCalled();
        });
    });
});



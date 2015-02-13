/* global describe, it, expect, spyOn */
define([
    'jsbuild'
], function () {
    'use strict';

    describe('Test jsbuild module', function () {

        it('caches original load method', function () {
            expect(require._load).toBeDefined();
        });

        it('loads external files', function (done) {
            spyOn(require, '_load').and.callThrough();

            require(['tests/assets/jsbuild/external'], function () {
                expect(require._load).toHaveBeenCalled();

                done();
            });
        });

        it('loads internal files', function (done) {
            spyOn(require, '_load').and.callThrough();

            require(['tests/assets/jsbuild/local'], function () {
                expect(require._load).not.toHaveBeenCalled();

                done();
            });
        });
    });
});

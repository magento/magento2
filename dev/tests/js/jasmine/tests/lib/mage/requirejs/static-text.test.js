/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'tests/assets/text/config',
    'text'
], function (config, text) {
    'use strict';

    var local = config.local,
        external = config.external;

    describe('extended text module', function () {

        it('exports reference to the module', function () {
            expect(text).toBeDefined();
        });

        it('caches original load method', function () {
            expect(text._load).toBeDefined();
        });

        it('loads external files', function (done) {
            spyOn(text, '_load').and.callThrough();

            require([
                external.path
            ], function (data) {
                var regExp = /\s+/g;

                expect(text._load).toHaveBeenCalled();
                expect(data.replace(regExp,' ')).toEqual(external.result.replace(regExp,' '));

                done();
            });
        });

        it('loads internal files', function (done) {
            spyOn(text, '_load').and.callThrough();

            require([
                local.path
            ], function (data) {
                expect(text._load).not.toHaveBeenCalled();
                expect(data).toEqual(local.result);

                done();
            });
        });
    });
});

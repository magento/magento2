/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'tests/assets/jsbuild/config',
    'jsbuild'
], function (config) {
    'use strict';

    var local = config.local,
        external = config.external;

    describe('jsbuild module', function () {

        it('caches original load method', function () {
            expect(require._load).toBeDefined();
        });

        it('loads external files', function (done) {
            spyOn(require, '_load').and.callThrough();

            require([
                external.path
            ], function (data) {
                expect(require._load).toHaveBeenCalled();
                expect(data).toEqual(external.result);

                done();
            });
        });

        it('loads internal files', function (done) {
            spyOn(require, '_load').and.callThrough();

            require([
                local.path
            ], function (data) {
                expect(require._load).not.toHaveBeenCalled();
                expect(data).toEqual(local.result);

                done();
            });
        });
    });
});

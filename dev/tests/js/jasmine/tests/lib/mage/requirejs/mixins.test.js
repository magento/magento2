/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
require.config({
    paths: {
        'mixins': 'mage/requirejs/mixins'
    }
});

define(['rjsResolver', 'mixins'], function (resolver, mixins) {
    'use strict';

    var context = {
        config: {}
    };

    describe('mixins module', function () {
        beforeEach(function (done) {
            // Wait for all modules to be loaded so they don't interfere with testing.
            resolver(function () {
                done();
            });
        });

        describe('processNames method', function () {
            beforeEach(function () {
                spyOn(mixins, 'processNames').and.callThrough();
                spyOn(mixins, 'hasMixins').and.callThrough();
            });

            it('gets called when module is both required and defined', function (done) {
                var name = 'tests/assets/mixins/defined-module',
                    dependencyName = 'tests/assets/mixins/defined-module-dependency';

                define(dependencyName, [], function () {});
                define(name, [dependencyName], function () {});

                require([name], function () {
                    expect(mixins.processNames.calls.argsFor(0)[0]).toEqual([]);
                    expect(mixins.processNames.calls.argsFor(1)[0]).toEqual([dependencyName]);
                    expect(mixins.processNames.calls.argsFor(2)[0]).toEqual([name]);
                    done();
                });
            });

            it('keeps name intact when it already contains another plugin', function () {
                mixins.hasMixins.and.returnValue(true);

                expect(mixins.processNames('plugin!module', context)).toBe('plugin!module');
            });

            it('keeps name intact when it has no mixins', function () {
                mixins.hasMixins.and.returnValue(false);

                expect(mixins.processNames('module', context)).toBe('module');
            });

            it('keeps names intact when they have no mixins', function () {
                mixins.hasMixins.and.returnValue(false);

                expect(mixins.processNames(['module'], context)).toEqual(['module']);
            });

            it('adds prefix to name when it has mixins', function () {
                mixins.hasMixins.and.returnValue(true);

                expect(mixins.processNames('module', context)).toBe('mixins!module');
            });

            it('adds prefix to name when it contains a relative path', function () {
                mixins.hasMixins.and.returnValue(false);

                expect(mixins.processNames('./module', context)).toBe('mixins!./module');
            });

            it('adds prefix to names when they contain a relative path', function () {
                mixins.hasMixins.and.returnValue(false);

                expect(mixins.processNames(['./module'], context)).toEqual(['mixins!./module']);
            });

            it('adds prefix to names when they have mixins', function () {
                mixins.hasMixins.and.returnValue(true);

                expect(mixins.processNames(['module'], context)).toEqual(['mixins!module']);
            });
        });

        describe('load method', function () {
            it('is not called when module has mixins', function (done) {
                var name = 'tests/assets/mixins/load-not-called';

                spyOn(mixins, 'hasMixins').and.returnValue(false);
                spyOn(mixins, 'load').and.callThrough();

                define(name, [], function () {});

                require([name], function () {
                    expect(mixins.load.calls.any()).toBe(false);
                    done();
                });
            });

            it('is called when module has mixins', function (done) {
                var name = 'tests/assets/mixins/load-called';

                spyOn(mixins, 'hasMixins').and.returnValue(true);
                spyOn(mixins, 'load').and.callThrough();

                define(name, [], function () {});

                require([name], function () {
                    expect(mixins.load.calls.mostRecent().args[0]).toEqual(name);
                    done();
                });
            });

            it('applies mixins for loaded module', function (done) {
                var name = 'tests/assets/mixins/mixins-applied',
                    mixinName = 'tests/assets/mixins/mixins-applied-ext';

                spyOn(mixins, 'hasMixins').and.returnValue(true);
                spyOn(mixins, 'load').and.callThrough();
                spyOn(mixins, 'getMixins').and.returnValue([mixinName]);

                define(name, [], function () {
                    return { value: 'original' };
                });

                define(mixinName, [], function () {
                    return function(module) {
                        module.value = 'changed';

                        return module;
                    };
                });

                require([name], function (module) {
                    expect(module.value).toBe('changed');
                    done();
                });
            });
        });
    });
});

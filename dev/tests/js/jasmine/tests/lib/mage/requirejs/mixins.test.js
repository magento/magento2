/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
// jscs:disable jsDoc

require.config({
    paths: {
        'mixins': 'mage/requirejs/mixins'
    }
});

define(['rjsResolver', 'mixins'], function (resolver, mixins) {
    'use strict';

    describe('mixins module', function () {
        beforeEach(function (done) {
            spyOn(mixins, 'hasMixins').and.callThrough();
            spyOn(mixins, 'getMixins').and.callThrough();
            spyOn(mixins, 'load').and.callThrough();

            // Wait for all modules to be loaded so they don't interfere with testing.
            resolver(function () {
                done();
            });
        });

        it('does not affect modules without mixins', function (done) {
            var name = 'tests/assets/mixins/no-mixins',
                mixinName = 'tests/assets/mixins/no-mixins-ext';

            mixins.hasMixins.and.returnValue(false);

            define(name, [], function () {
                return {
                    value: 'original'
                };
            });

            define(mixinName, [], function () {
                return function (module) {
                    module.value = 'changed';

                    return module;
                };
            });

            require([name], function (module) {
                expect(module.value).toBe('original');

                done();
            });
        });

        it('does not affect modules that are loaded with plugins', function (done) {
            var name = 'plugin!tests/assets/mixins/no-mixins',
                mixinName = 'tests/assets/mixins/no-mixins-ext';

            mixins.hasMixins.and.returnValue(true);
            mixins.getMixins.and.returnValue([mixinName]);

            define('plugin', [], function () {
                return {
                    load: function (module, req, onLoad) {
                        req(module, onLoad);
                    }
                };
            });

            define(name, [], function () {
                return {
                    value: 'original'
                };
            });

            define(mixinName, [], function () {
                return function (module) {
                    module.value = 'changed';

                    return module;
                };
            });

            require([name], function (module) {
                expect(module.value).toBe('original');

                done();
            });
        });

        it('applies mixins for normal module with mixins', function (done) {
            var name = 'tests/assets/mixins/mixins-applied',
                mixinName = 'tests/assets/mixins/mixins-applied-ext';

            mixins.hasMixins.and.returnValue(true);
            mixins.getMixins.and.returnValue([mixinName]);

            define(name, [], function () {
                return {
                    value: 'original'
                };
            });

            define(mixinName, [], function () {
                return function (module) {
                    module.value = 'changed';

                    return module;
                };
            });

            require([name], function (module) {
                expect(module.value).toBe('changed');

                done();
            });
        });

        it('applies mixins for module that is a dependency', function (done) {
            var name = 'tests/assets/mixins/module-with-dependency',
                dependencyName = 'tests/assets/mixins/dependency-module',
                mixinName = 'tests/assets/mixins/dependency-module-ext';

            mixins.hasMixins.and.returnValue(true);
            mixins.getMixins.and.returnValue([mixinName]);

            define(dependencyName, [], function () {
                return {
                    value: 'original'
                };
            });

            define(name, [dependencyName], function (module) {
                expect(module.value).toBe('changed');

                done();

                return {};
            });

            define(mixinName, [], function () {
                return function (module) {
                    module.value = 'changed';

                    return module;
                };
            });

            require([name], function () {});
        });

        it('applies mixins for module that is a relative dependency', function (done) {
            var name = 'tests/assets/mixins/module-with-relative-dependency',
                dependencyName = 'tests/assets/mixins/relative-module',
                mixinName = 'tests/assets/mixins/relative-module-ext';

            mixins.hasMixins.and.returnValue(true);
            mixins.getMixins.and.returnValue([mixinName]);

            define(dependencyName, [], function () {
                return {
                    value: 'original'
                };
            });

            define(name, ['./relative-module'], function (module) {
                expect(module.value).toBe('changed');

                done();

                return {};
            });

            define(mixinName, [], function () {
                return function (module) {
                    module.value = 'changed';

                    return module;
                };
            });

            require([name], function () {});
        });
    });
});

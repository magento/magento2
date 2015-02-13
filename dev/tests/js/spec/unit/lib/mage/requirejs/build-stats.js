/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'buildStats'
], function (buildStats) {
    'use strict';

    var keysOf          = Object.keys.bind(Object),
        stringify       = JSON.stringify.bind(JSON),
        get             = localStorage.getItem.bind(localStorage),
        set             = localStorage.setItem.bind(localStorage),
        clear           = localStorage.removeItem.bind(localStorage),
        moduleSample    = 'define([], function(){ \'use strict\'; return true; });',
        firstBundle,
        secondBundle;

    firstBundle = {
        'module-1.js': moduleSample,
        'module-2.js': moduleSample
    };

    secondBundle = {
        'module-3.js': moduleSample,
        'module-4.js': moduleSample
    };

    /**
     * Returns JSON.parsed value extracted from localStorage by 'key' key.
     *
     * @param  {String} key
     * @return {*}
     */
    function parsed(key) {
        return JSON.parse(get(key));
    }

    /**
     * Overrides or extends 'target' properties with properties of futher passed objects.
     *
     * @param  {Object} target
     * @return {Object}
     */
    function extend(target) {
        var defs = Array.prototype.slice.call(arguments, 1),
            keys;

        defs.forEach(function (obj) {
            keys = Object.keys(obj);

            keys.forEach(function (key) {
                target[key] = obj[key];
            });
        });

        return target;
    }

    describe('buildStats module', function () {
        beforeEach(function () {
            clear('all');
            clear('used');
        });

        describe('register method', function () {
            it('merges passed object\'s keys with array under localStorage\'s \'all\' namespace', function () {
                var merged = keysOf(firstBundle).concat(keysOf(secondBundle));

                buildStats.register(firstBundle);
                buildStats.register(secondBundle);

                expect(parsed('all')).toEqual(merged);
            });

            it('removes duplicated entries', function () {
                var mergedBundle = extend({}, firstBundle, secondBundle);

                buildStats.register(firstBundle);
                buildStats.register(mergedBundle);

                expect(parsed('all')).toEqual(keysOf(mergedBundle));
            });
        });

        describe('utilize method', function () {
            it('stores passed string to array under localStorage\'s \'used\' namespace', function () {
                var str = keysOf(firstBundle)[0];

                buildStats.utilize(str);

                expect(parsed('used')).toEqual([str]);
            });

            it('removes duplicated entries', function () {
                var expected    = keysOf(firstBundle),
                    str         = expected[0];

                set('used', stringify(expected));

                buildStats.utilize(str);

                expect(parsed('used')).toEqual(expected);
            });
        });

        describe('getAll method', function () {
            it('returns JSON.parsed content of localStorage\'s \'all\' namespace', function () {
                var expected = keysOf(firstBundle);

                set('all', stringify(expected));

                expect(buildStats.getAll()).toEqual(expected);
            });
        });

        describe('getUsed method', function () {
            it('returns JSON.parsed content of localStorage\'s \'used\' namespace', function () {
                var expected = keysOf(secondBundle);

                set('used', stringify(expected));

                expect(buildStats.getUsed()).toEqual(expected);
            });
        });

        describe('getUnused method', function () {
            it('compares results of getAll and getUsed methods and returns the difference', function () {
                var modules = keysOf(firstBundle);

                buildStats.register(firstBundle);

                buildStats.utilize(modules[0]);

                expect(buildStats.getUnused()).toEqual([modules[1]]);
            });
        });
    });
});

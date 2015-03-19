/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'statistician',
    'underscore'
], function (statistician, _) {
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
     * @returns {*}
     */
    function parsed(key) {
        return JSON.parse(get(key));
    }

    describe('mage/requirejs/statistician', function () {
        beforeEach(function () {
            clear('all');
            clear('used');
        });

        describe('"collect" method', function () {
            it('merges passed object\'s keys with array under localStorage\'s "all" namespace', function () {
                var merged = keysOf(firstBundle).concat(keysOf(secondBundle));

                statistician.collect(firstBundle);
                statistician.collect(secondBundle);

                expect(parsed('all')).toEqual(merged);
            });

            it('removes duplicated entries', function () {
                var mergedBundle = _.extend({}, firstBundle, secondBundle);

                statistician.collect(firstBundle);
                statistician.collect(mergedBundle);

                expect(parsed('all')).toEqual(keysOf(mergedBundle));
            });
        });

        describe('"utilize" method', function () {
            it('stores passed string to array under localStorage\'s "used" namespace', function () {
                var str = keysOf(firstBundle)[0];

                statistician.utilize(str);

                expect(parsed('used')).toEqual([str]);
            });

            it('removes duplicated entries', function () {
                var expected = keysOf(firstBundle);

                set('used', stringify(expected));

                statistician.utilize(expected[0]);

                expect(parsed('used')).toEqual(expected);
            });
        });

        describe('"getAll" method', function () {
            it('returns JSON.parsed content of localStorage\'s "all" namespace', function () {
                var expected = keysOf(firstBundle);

                set('all', stringify(expected));

                expect(statistician.getAll()).toEqual(expected);
            });
        });

        describe('"getUsed" method', function () {
            it('returns JSON.parsed content of localStorage\'s "used" namespace', function () {
                var expected = keysOf(secondBundle);

                set('used', stringify(expected));

                expect(statistician.getUsed()).toEqual(expected);
            });
        });

        describe('"getUnused" method', function () {
            it('compares results of getAll and getUsed methods and returns the difference', function () {
                var modules = keysOf(firstBundle);

                statistician.collect(firstBundle);

                statistician.utilize(modules[0]);

                expect(statistician.getUnused()).toEqual([modules[1]]);
            });
        });

        describe('"clear" method', function () {
            it('clears "used" and "all" namespaces of localStorage', function () {
                localStorage.setItem('all', 'someString');
                localStorage.setItem('used', 'someString');

                statistician.clear();

                expect(localStorage.getItem('all')).toEqual(null);
                expect(localStorage.getItem('used')).toEqual(null);
            });
        });
    });
});

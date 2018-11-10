/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Rule/conditions-data-normalizer'
], function ($, Normalizer) {
    'use strict';

    describe('Magento_Rule/conditions-data-normalizer', function () {
        var normalizer;

        beforeEach(function () {
            normalizer = new Normalizer();
        });

        it('Check for empty object when input is a falsey value', function () {
            expect(normalizer.normalize('')).toEqual({});
            expect(normalizer.normalize()).toEqual({});
            expect(normalizer.normalize(false)).toEqual({});
            expect(normalizer.normalize(null)).toEqual({});
            expect(normalizer.normalize(0)).toEqual({});
        });

        it('Check single level normalization.', function () {
            var normal = normalizer.normalize({
                foo: 'bar',
                bar: 123
            });

            expect(normal.foo).toEqual('bar');
            expect(normal.bar).toEqual(123);
        });

        it('Check one sub-level of normalization.', function () {
            var normal = normalizer.normalize({
                'foo[value]': 'bar',
                'foo[name]': 123
            });

            expect(normal.foo.value).toEqual('bar');
            expect(normal.foo.name).toEqual(123);
        });

        it('Check two sub-levels of normalization.', function () {
            var normal = normalizer.normalize({
                'foo[prefix][value]': 'bar',
                'foo[prefix][name]': 123
            });

            expect(normal.foo.prefix.value).toEqual('bar');
            expect(normal.foo.prefix.name).toEqual(123);
        });

        it('Check that numeric types don\'t get converted to array form.', function () {
            var normal = normalizer.normalize({
                'foo[1][name]': 'bar',
                'foo[1][value]': 123,
                'foo[1--1]': 321
            });

            expect(normal.foo['1'].name).toEqual('bar');
            expect(normal.foo['1'].value).toEqual(123);
            expect(normal.foo['1--1']).toEqual(321);
        });

        it('Check keys containing a dot are normalized', function () {
            var normal = normalizer.normalize({
                'foo[1][name.foo]': 'bar',
                'foo[1][value.foo]': 123,
                'foo[1--1]': 321
            });

            expect(normal.foo['1']['name.foo']).toEqual('bar');
            expect(normal.foo['1']['value.foo']).toEqual(123);
            expect(normal.foo['1--1']).toEqual(321);
        });
    });
});

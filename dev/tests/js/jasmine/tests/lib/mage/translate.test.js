/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    // be careful with test variation order as one variation can affect another one
    describe('Test for mage/translate jQuery plugin', function () {
        it('works with one string as parameter', function () {
            $.mage.translate.add('Hello World!');
            expect('Hello World!').toEqual($.mage.translate.translate('Hello World!'));
        });
        it('works with translation alias __', function () {
            $.mage.translate.add('Hello World!');
            expect('Hello World!').toEqual($.mage.__('Hello World!'));
        });
        it('works with one array as parameter', function () {
            $.mage.translate.add(['Hello World!', 'Bonjour tout le monde!']);
            expect('Hello World!').toEqual($.mage.translate.translate('Hello World!'));
        });
        it('works with one object as parameter', function () {
            var translation = {
                'Hello World!': 'Bonjour tout le monde!'
            };

            $.mage.translate.add(translation);
            expect(translation['Hello World!']).toEqual($.mage.translate.translate('Hello World!'));

            translation = {
                'Hello World!': 'Hallo Welt!',
                'Some text with symbols!-+"%#*': 'Ein Text mit Symbolen!-+"%#*',
                'Text with empty value': ''
            };

            $.mage.translate.add(translation);
            $.each(translation, function (key) {
                expect(translation[key]).toEqual($.mage.translate.translate(key));
            });
        });
        it('works with two string as parameter', function () {
            $.mage.translate.add('Hello World!', 'Bonjour tout le monde!');
            expect('Bonjour tout le monde!').toEqual($.mage.translate.translate('Hello World!'));
        });
    });

});

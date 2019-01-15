/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Catalog/js/product/remaining-characters',
    'jquery'
], function (remainingCharacters, $) {
    'use strict';

    describe('Magento_Catalog/js/product/remaining-characters', function () {
        var widget,
            note;

        beforeEach(function () {
            widget = $('<input type="text" data-selector="options[1]"/>');
            note = $('<p class="note note_1"><span class="character-counter"></span></p>');
            $('body').append(widget).append(note);

            widget.remainingCharacters({
                maxLength: '10',
                noteSelector: '.note_1',
                counterSelector: '.note_1 .character-counter'
            });
        });

        afterEach(function () {
            widget.remove();
            note.remove();
        });

        describe('Note text is updated on input change', function () {
            it('check empty input', function () {
                var testData = {
                    input: '',
                    action: 'change',
                    expectedText: '(10 remaining)'
                };

                widget.val(testData.input);
                widget.trigger(testData.action);
                expect(note.find('.character-counter').text()).toBe(testData.expectedText);
            });

            it('check input length less than character limit', function () {
                var testData = {
                    input: 'abc',
                    action: 'change',
                    expectedText: '(7 remaining)'
                };

                widget.val(testData.input);
                widget.trigger(testData.action);
                expect(note.find('.character-counter').text()).toBe(testData.expectedText);
            });

            it('check input length equals character limit', function () {
                var testData = {
                    input: 'abcdefghij',
                    action: 'paste',
                    expectedText: '(0 remaining)'
                };

                widget.val(testData.input);
                widget.trigger(testData.action);
                expect(note.find('.character-counter').text()).toBe(testData.expectedText);
            });

            it('check input length greater than character limit', function () {
                var testData = {
                    input: 'abcdefghijkl',
                    action: 'change',
                    expectedText: '(2 too many)'
                };

                widget.val(testData.input);
                widget.trigger(testData.action);
                expect(note.find('.character-counter').text()).toBe(testData.expectedText);
            });
        });
    });
});

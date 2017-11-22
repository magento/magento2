/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/accordion'
], function ($) {
    'use strict';

    describe('Test for mage/accordion jQuery plugin', function () {
        it('check if accordion can be initialized', function () {
            var accordion = $('<div/>');

            accordion.accordion();
            expect(accordion.is(':mage-accordion')).toBeTruthy();

            accordion.accordion('destroy');
            expect(accordion.is(':mage-accordion')).toBeFalsy();
        });
        it('check one-collapsible element accordion', function () {
            var accordion = $('<div/>'),
                title1 = $('<div data-role="collapsible"></div>').appendTo(accordion),
                content1 = $('<div data-role="content"></div>').appendTo(accordion),
                title2 = $('<div data-role="collapsible"></div>').appendTo(accordion),
                content2 = $('<div data-role="content"></div>').appendTo(accordion);

            accordion.appendTo('body');

            accordion.accordion();

            expect(accordion.is(':mage-accordion')).toBeTruthy();

            expect(content1.is(':visible')).toBeTruthy();
            expect(content2.is(':hidden')).toBeTruthy();

            title2.trigger('click');

            expect(content1.is(':hidden')).toBeTruthy();
            expect(content2.is(':visible')).toBeTruthy();

            title1.trigger('click');

            expect(content1.is(':visible')).toBeTruthy();
            expect(content2.is(':hidden')).toBeTruthy();

            accordion.accordion('destroy');

            expect(accordion.is(':mage-accordion')).toBeFalsy();
        });
        it('check multi-collapsible element accordion', function () {
            var accordion = $('<div/>'),
                title1 = $('<div data-role="collapsible"></div>').appendTo(accordion),
                content1 = $('<div data-role="content"></div>').appendTo(accordion),
                title2 = $('<div data-role="collapsible"></div>').appendTo(accordion),
                content2 = $('<div data-role="content"></div>').appendTo(accordion);

            accordion.appendTo('body');

            accordion.accordion({
                multipleCollapsible: true
            });

            expect(accordion.is(':mage-accordion')).toBeTruthy();
            expect(content1.is(':visible')).toBeTruthy();
            expect(content2.is(':hidden')).toBeTruthy();

            $(title1).trigger('click');
            expect(content1.is(':visible')).toBeTruthy();
            expect(content2.is(':hidden')).toBeTruthy();

            $(title2).trigger('click');
            expect(content1.is(':visible')).toBeTruthy();
            expect(content2.is(':visible')).toBeTruthy();

            accordion.accordion('destroy');
            expect(accordion.is(':mage-accordion')).toBeFalsy();
        });
    });
});

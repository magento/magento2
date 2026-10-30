/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/tabs',
    'text!tests/assets/lib/web/mage/tabs.html'
], function ($, tabs, tabsTmpl) {
    'use strict';

    describe('mage/tabs', function () {
        var tabsSelector = '#tabs';

        beforeEach(function () {
            var $tabs = $(tabsTmpl);

            $('body').append($tabs);
        });

        afterEach(function () {
            $(tabsSelector).remove();
            $(tabsSelector).tabs('destroy');
        });

        it('Check tabs inited', function () {
            var $tabs = $(tabsSelector).tabs();

            expect($tabs.is(':mage-tabs')).toBe(true);
        });

        it('Check tabs collapsible inited', function () {
            var $title1 = $('#title1'),
                $title2 = $('#title2');

            $(tabsSelector).tabs();

            expect($title1.is(':mage-collapsible')).toBe(true);
            expect($title2.is(':mage-collapsible')).toBe(true);
        });

        it('Check tabs active', function () {
            var $content1 = $('#content1'),
                $content2 = $('#content2');

            $(tabsSelector).tabs({
                active: 1
            });

            expect($content1.is(':hidden')).toBe(true);
            expect($content2.is(':visible')).toBe(true);
        });

        it('Check tabs closing others tabs when one gets activated', function () {
            var $title2 = $('#title2'),
                $content1 = $('#content1'),
                $content2 = $('#content2');

            $(tabsSelector).tabs();

            expect($content1.is(':visible')).toBe(true);
            expect($content2.is(':hidden')).toBe(true);

            $title2.trigger('click');

            expect($content1.is(':hidden')).toBe(true);
            expect($content2.is(':visible')).toBe(true);
        });

        it('Check tabs enable,disable,activate,deactivate options', function () {
            var $title1 = $('#title1'),
                $content1 = $('#content1'),
                $tabs = $(tabsSelector).tabs();

            expect($content1.is(':visible')).toBe(true);
            $tabs.tabs('deactivate', 0);
            expect($content1.is(':hidden')).toBe(true);
            $tabs.tabs('activate', 0);
            expect($content1.is(':visible')).toBe(true);
            $tabs.tabs('disable', 0);
            expect($content1.is(':hidden')).toBe(true);
            $title1.trigger('click');
            expect($content1.is(':hidden')).toBe(true);
            $tabs.tabs('enable', 0);
            expect($content1.is(':visible')).toBe(true);
            $title1.trigger('click');
            expect($content1.is(':visible')).toBe(true);
        });
    });

    describe('mage/tabs URL anchor handling', function () {
        var originalUrl = window.location.href,
            $tabs;

        function setHash(hash) {
            window.history.replaceState(null, '', window.location.pathname + window.location.search + hash);
        }

        beforeEach(function () {
            jasmine.clock().install();
            $tabs = $(
                '<div id="anchor-tabs">' +
                    '<div id="anchor-title-additional" data-role="collapsible"></div>' +
                    '<div id="additional" data-role="content"></div>' +
                    '<div id="anchor-title-custom" data-role="collapsible"></div>' +
                    '<div id="custom" data-role="content" style="display: none;"></div>' +
                    '<div id="anchor-title-reviews" data-role="collapsible"></div>' +
                    '<div id="reviews" data-role="content" style="display: none;">' +
                        '<div id="review-form"></div>' +
                    '</div>' +
                '</div>'
            ).appendTo('body');
            $tabs.find('[data-role=content]').each(function () {
                spyOn(this, 'scrollIntoView');
            });
            spyOn($tabs.find('#review-form')[0], 'scrollIntoView');
        });

        afterEach(function () {
            $tabs.tabs('destroy');
            $tabs.remove();
            window.history.replaceState(null, '', originalUrl);
            jasmine.clock().uninstall();
        });

        it('opens and scrolls to the reviews tab placed last', function () {
            setHash('#reviews');
            $tabs.tabs({
                scrollToAnchor: true
            });
            jasmine.clock().tick(1);

            expect($('#reviews').is(':visible')).toBe(true);
            expect($('#additional').is(':hidden')).toBe(true);
            expect($('#custom').is(':hidden')).toBe(true);
            expect($('#reviews')[0].scrollIntoView).toHaveBeenCalledTimes(1);
            expect($('#additional')[0].scrollIntoView).not.toHaveBeenCalled();
        });

        it('opens and scrolls to any other tab targeted by the anchor', function () {
            setHash('#custom');
            $tabs.tabs({
                scrollToAnchor: true
            });
            jasmine.clock().tick(1);

            expect($('#custom').is(':visible')).toBe(true);
            expect($('#additional').is(':hidden')).toBe(true);
            expect($('#reviews').is(':hidden')).toBe(true);
            expect($('#custom')[0].scrollIntoView).toHaveBeenCalledTimes(1);
        });

        it('opens the tab containing a nested anchor and scrolls to that element', function () {
            setHash('#review-form');
            $tabs.tabs({
                scrollToAnchor: true
            });
            jasmine.clock().tick(1);

            expect($('#reviews').is(':visible')).toBe(true);
            expect($('#additional').is(':hidden')).toBe(true);
            expect($('#review-form')[0].scrollIntoView).toHaveBeenCalled();
            expect($('#reviews')[0].scrollIntoView).not.toHaveBeenCalled();
        });

        it('opens the targeted tab without scrolling when scrollToAnchor is not set', function () {
            setHash('#reviews');
            $tabs.tabs();
            jasmine.clock().tick(1);

            expect($('#reviews').is(':visible')).toBe(true);
            expect($('#additional').is(':hidden')).toBe(true);
            expect($('#reviews')[0].scrollIntoView).not.toHaveBeenCalled();
        });

        it('keeps the default tab and does not scroll when the anchor matches no tab', function () {
            setHash('#missing-anchor');
            $tabs.tabs({
                scrollToAnchor: true
            });
            jasmine.clock().tick(1);

            expect($('#additional').is(':visible')).toBe(true);
            expect($('#reviews').is(':hidden')).toBe(true);
            $tabs.find('[data-role=content]').each(function () {
                expect(this.scrollIntoView).not.toHaveBeenCalled();
            });
        });
    });
});

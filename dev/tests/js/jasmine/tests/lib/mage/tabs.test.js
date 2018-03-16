/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'jquery/ui',
    'mage/tabs',
    'text!tests/assets/lib/web/mage/tabs.html'
], function ($, ui, tabs, tabsTmpl) {
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
});

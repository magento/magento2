/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/menu',
    'text!tests/assets/lib/web/mage/menu.html'
], function ($, menu, menuTmpl) {
    'use strict';

    describe('mage/menu', function () {
        describe('Menu expanded', function () {
            var menuSelector = '#menu';

            beforeEach(function () {
                var $menu = $(menuTmpl);

                $('body').append($menu);
            });

            afterEach(function () {
                $(menuSelector).remove();
            });

            it('Check that menu expanded', function () {
                var $menu = $(menuSelector),
                    $menuItems = $menu.find('li'),
                    $submenu = $menuItems.find('ul');

                menu.menu({
                    expanded: true
                }, $menu);
                expect($submenu.hasClass('expanded')).toBe(true);
            });
        });

        describe('Menu hover event', function () {
            var menuSelector = '#menu',
                $menu;

            beforeEach(function () {
                var $menuObject = $(menuTmpl);

                $('body').append($menuObject);
                $menu = $(menuSelector).menu({
                    delay: 0,
                    showDelay: 0,
                    hideDelay: 0
                });
            });

            afterEach(function () {
                $(menuSelector).remove();
            });

            it('Check that menu expanded', function (done) {
                var $menuItem = $menu.find('li.test-menu-item'),
                    $submenu = $menuItem.find('ul');

                $menuItem.trigger('mouseover');
                setTimeout(function () {
                    expect($submenu.attr('aria-expanded')).toBe('true');
                    $menuItem.trigger('mouseout');
                    setTimeout(function () {
                        expect($submenu.attr('aria-expanded')).toBe('false');
                        done();
                    }, 300);
                }, 300);
            });
        });

        describe('Menu keyboard focus', function () {
            var menuSelector = '#menu',
                $menu;

            beforeEach(function () {
                $('body').append($(menuTmpl));
                $menu = $(menuSelector).menu({
                    delay: 0,
                    showDelay: 0,
                    hideDelay: 0
                });
            });

            afterEach(function () {
                $(menuSelector).remove();
            });

            it('Check that focused item gets ui-state-focus and loses it on blur', function () {
                var instance = $menu.data('mage-menu'),
                    $item = $menu.find('li.test-menu-item'),
                    $link = $item.children('a');

                instance.focus($.Event('keydown'), $item);

                expect($link.hasClass('ui-state-active')).toBe(true);
                expect($link.hasClass('ui-state-focus')).toBe(true);

                instance.blur($.Event('keydown'));

                expect($link.hasClass('ui-state-focus')).toBe(false);
            });

            it('Check that ui-state-focus moves with the focus', function () {
                var instance = $menu.data('mage-menu'),
                    $items = $menu.children('li').filter(function () {
                        return $(this).children('a').length > 0;
                    }),
                    $first = $items.eq(0),
                    $second = $items.eq(1);

                instance.focus($.Event('keydown'), $first);
                instance.focus($.Event('keydown'), $second);

                expect($first.children('a').hasClass('ui-state-focus')).toBe(false);
                expect($second.children('a').hasClass('ui-state-focus')).toBe(true);
            });
        });

        describe('Menu navigation', function () {
            var menuSelector = '#menu',
                $menu;

            beforeEach(function () {
                var $menuObject = $(menuTmpl);

                $('body').append($menuObject);
                $menu = $(menuSelector).menu();
            });

            afterEach(function () {
                $(menuSelector).remove();
            });

            it('Check max item limit', function () {
                var $menuItems;

                $menu.navigation({
                    maxItems: 3
                });
                $menuItems = $menu.find('li:visible');

                expect($menuItems.length).toBe(4);
            });

            it('Check that More Menu item will be added', function () {
                $menu.navigation({
                    responsive: 'onResize'
                });

                expect($('body').find('.ui-menu-more').length).toBeGreaterThan(0);
            });
        });
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

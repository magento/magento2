/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {Object} options
     */
    $.fn.dropdown = function (options) {
        var defaults = {
                parent: null,
                autoclose: true,
                btnArrow: '.arrow',
                menu: '[data-target="dropdown"]',
                activeClass: 'active'
            },
            actionElem = $(this),
            self = this;

        options = $.extend(defaults, options);
        actionElem = $(this);
        self = this;

        /**
         * @param {HTMLElement} elem
         */
        this.openDropdown = function (elem) {
            elem
                .addClass(options.activeClass)
                .attr('aria-expanded', true)
                .parent()
                    .addClass(options.activeClass);

            elem.parent()
                .find(options.menu)
                .attr('aria-hidden', false);

            $(options.btnArrow, elem).text('-');
        };

        /**
         * @param {HTMLElement} elem
         */
        this.closeDropdown = function (elem) {
            elem.removeClass(options.activeClass)
                .attr('aria-expanded', false)
                .parent()
                    .removeClass(options.activeClass);

            elem.parent()
                .find(options.menu)
                .attr('aria-hidden', true);

            $(options.btnArrow, elem).text('+');
        };

        /**
         * Reset all dropdowns.
         *
         * @param {Object} param
         */
        this.reset = function (param) {
            var params = param || {},
                dropdowns = params.elems || actionElem;

            dropdowns.each(function (index, elem) {
                self.closeDropdown($(elem));
            });
        };

        /* document Event bindings */
        if (options.autoclose === true) {
            $(document).on('click.hideDropdown', this.reset);
            $(document).on('keyup.hideDropdown', function (e) {
                var ESC_CODE = '27';

                if (e.keyCode == ESC_CODE) { //eslint-disable-line eqeqeq
                    self.reset();
                }
            });
        }

        if (options.events) {
            $.each(options.events, function (index, event) {
                $(document).on(event.name, event.selector, event.action);
            });
        }

        return this.each(function () {
            var elem = $(this),
                parent = $(options.parent).length > 0 ? $(options.parent) : elem.parent(),
                menu = $(options.menu, parent) || $('.dropdown-menu', parent);

            // ARIA (adding aria attributes)
            if (menu.length) {
                elem.attr('aria-haspopup', true);
            }

            if (!elem.hasClass(options.activeClass)) {
                elem.attr('aria-expanded', false);
                menu.attr('aria-hidden', true);
            } else {
                elem.attr('aria-expanded', true);
                menu.attr('aria-hidden', false);
            }

            if (!elem.is('a, button')) {
                elem.attr('role', 'button');
                elem.attr('tabindex', 0);
            }

            if (elem.attr('data-trigger-keypress-button')) {
                elem.on('keypress', function (e) {
                    var keyCode = e.keyCode || e.which,
                        ENTER_CODE = 13;

                    if (keyCode === ENTER_CODE) {
                        e.preventDefault();
                        elem.trigger('click.toggleDropdown');
                    }
                });
            }

            elem.on('click.toggleDropdown', function () {
                var el = actionElem;

                if (options.autoclose === true) {
                    actionElem = $();
                    $(document).trigger('click.hideDropdown');
                    actionElem = el;
                }

                self[el.hasClass(options.activeClass) ? 'closeDropdown' : 'openDropdown'](elem);

                return false;
            });
        });
    };

    return function (data, el) {
        $(el).dropdown(data);
    };
});

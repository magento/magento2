/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    "jquery"
], function($){
    'use strict';

    $.fn.dropdown = function(options) {
        var defaults = {
            parent: null,
            autoclose: true,
            btnArrow: '.arrow',
            menu: '[data-target="dropdown"]',
            activeClass: 'active'
        };

        options = $.extend(defaults, options);
        var actionElem = $(this),
            self = this;

        this.openDropdown = function(elem) {
            elem
                .addClass(options.activeClass)
                .parent()
                    .addClass(options.activeClass);

            $(options.btnArrow, elem).text('-');
        };

        this.closeDropdown = function(elem) {
            elem
                .removeClass(options.activeClass)
                .parent()
                    .removeClass(options.activeClass);

            $(options.btnArrow, elem).text('+');
        };

        /* Reset all dropdowns */
        this.reset = function(param) {
            var params = param || {},
                dropdowns = params.elems || actionElem;

            dropdowns.each(function(index, elem) {
                self.closeDropdown($(elem));
            });
        };

        /* document Event bindings */
        if(options.autoclose === true) {
            $(document).on('click.hideDropdown', this.reset);
            $(document).on('keyup.hideDropdown', function(e) {
                var ESC_CODE = '27';

                if (e.keyCode == ESC_CODE) {
                    self.reset();
                }
            });
        }

        if (options.events) {
            $.each(options.events, function(index, event) {
                $(document).on(event.name, event.selector, event.action);
            });
        }

        return this.each(function() {
            var elem = $(this),
                parent = $(options.parent) || elem.parent(),
                menu = $(options.menu, parent) || $('.dropdown-menu', parent);

            elem.on('click.toggleDropdown', function() {
                if(options.autoclose === true) {
                    self.reset({elems: actionElem.not(elem)});
                }
                self[elem.hasClass('active') ? 'closeDropdown' : 'openDropdown'](elem);

                return false;
            });
        });
    };

    $(document).ready(function() {
        $('[data-toggle="dropdown"]').dropdown();
    });
});
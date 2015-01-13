/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    
    return function(data, el){
        $(el).dropdown(data);
    };
});
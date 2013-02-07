;(function($, document) {
    'use strict';

    $.fn.dropdown = function(options) {
        var defaults = {
            parent: null,
            btnArrow: '.arrow',
            activeClass: 'active'
        };

        var options = $.extend({}, defaults, options);
        var actionElem = $(this),
            self = this;

        this.openDropdown = function(elem) {
            elem
                .addClass(options.activeClass)
                .parent()
                    .addClass(options.activeClass);

            $(options.btnArrow, elem).text('▲');
        };

        this.closeDropdown = function(elem) {
            elem
                .removeClass(options.activeClass)
                .parent()
                    .removeClass(options.activeClass);

            $(options.btnArrow, elem).text('▼');
        };

        /* Reset all dropdowns */
        this.reset = function(params) {
            var params = params || {},
                dropdowns = params.elems || actionElem;

            dropdowns.each(function(index, elem) {
                self.closeDropdown($(elem));
            });
        };

        /* document Event bindings */
        $(document).on('click.hideDropdown', this.reset);
        $(document).on('keyup.hideDropdown', function(e) {
            var ESC_CODE = '27';

            if (e.keyCode == ESC_CODE) {
                self.reset();
            }
        });

        return this.each(function() {
            var elem = $(this),
                parent = elem.parent(),
                menu = $('.dropdown-menu', parent);

            elem.on('click.toggleDropdown', function() {
                self.reset({elems: actionElem.not(elem)});
                self[elem.hasClass('active') ? 'closeDropdown' : 'openDropdown'](elem);

                return false;
            });

            menu.on('click.preventMenuClosing', function(e) {
                e.stopPropagation();
            });
        });
    };

    $(document).ready(function() {
        $('[data-toggle="dropdown"]').dropdown();
    });
})(window.jQuery, document);
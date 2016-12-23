/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('mage.menu', {
        widgetEventPrefix: 'menu',
        version: '1.10.1',
        defaultElement: '<ul>',
        delay: 300,
        options: {
            icons: {
                submenu: 'ui-icon-carat-1-e'
            },
            menus: 'ul',
            position: {
                my: 'left top',
                at: 'right top'
            },
            role: 'menu',

            // callbacks
            blur: null,
            focus: null,
            select: null
        },

        /**
         * @private
         */
        _create: function () {
            this.activeMenu = this.element;
            // flag used to prevent firing of the click handler
            // as the event bubbles up through nested menus
            this.mouseHandled = false;
            this.element
                .uniqueId()
                .addClass('ui-menu ui-widget ui-widget-content ui-corner-all')
                .toggleClass('ui-menu-icons', !!this.element.find('.ui-icon').length)
                .attr({
                    role: this.options.role,
                    tabIndex: 0
                })
                // need to catch all clicks on disabled menu
                // not possible through _on
                .bind('click' + this.eventNamespace, $.proxy(function (event) {
                    if (this.options.disabled) {
                        event.preventDefault();
                    }
                }, this));

            if (this.options.disabled) {
                this.element
                    .addClass('ui-state-disabled')
                    .attr('aria-disabled', 'true');
            }

            this._on({
                /**
                 * Prevent focus from sticking to links inside menu after clicking
                 * them (focus should always stay on UL during navigation).
                 */
                'mousedown .ui-menu-item > a': function (event) {
                    event.preventDefault();
                },

                /**
                 * Prevent focus from sticking to links inside menu after clicking
                 * them (focus should always stay on UL during navigation).
                 */
                'click .ui-state-disabled > a': function (event) {
                    event.preventDefault();
                },

                /**
                 * @param {jQuery.Event} event
                 */
                'click .ui-menu-item:has(a)': function (event) {
                    var target = $(event.target).closest('.ui-menu-item');

                    if (!this.mouseHandled && target.not('.ui-state-disabled').length) {
                        this.mouseHandled = true;

                        this.select(event);
                        // Open submenu on click
                        if (target.has('.ui-menu').length) {
                            this.expand(event);
                        } else if (!this.element.is(':focus')) {
                            // Redirect focus to the menu
                            this.element.trigger('focus', [true]);

                            // If the active item is on the top level, let it stay active.
                            // Otherwise, blur the active item since it is no longer visible.
                            if (this.active && this.active.parents('.ui-menu').length === 1) { //eslint-disable-line
                                clearTimeout(this.timer);
                            }
                        }
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                'mouseenter .ui-menu-item': function (event) {
                    var target = $(event.currentTarget);

                    // Remove ui-state-active class from siblings of the newly focused menu item
                    // to avoid a jump caused by adjacent elements both having a class with a border
                    target.siblings().children('.ui-state-active').removeClass('ui-state-active');
                    this.focus(event, target);
                },
                mouseleave: 'collapseAll',
                'mouseleave .ui-menu': 'collapseAll',

                /**
                 * @param {jQuery.Event} event
                 * @param {*} keepActiveItem
                 */
                focus: function (event, keepActiveItem) {
                    // If there's already an active item, keep it active
                    // If not, activate the first item
                    var item = this.active || this.element.children('.ui-menu-item').eq(0);

                    if (!keepActiveItem) {
                        this.focus(event, item);
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                blur: function (event) {
                    this._delay(function () {
                        if (!$.contains(this.element[0], this.document[0].activeElement)) {
                            this.collapseAll(event);
                        }
                    });
                },
                keydown: '_keydown'
            });

            this.refresh();

            // Clicks outside of a menu collapse any open menus
            this._on(this.document, {
                /**
                 *
                 * @param {jQuery.Event} event
                 */
                click: function (event) {
                    if (!$(event.target).closest('.ui-menu').length) {
                        this.collapseAll(event);
                    }

                    // Reset the mouseHandled flag
                    this.mouseHandled = false;
                }
            });
        },

        /**
         * @private
         */
        _destroy: function () {
            // Destroy (sub)menus
            this.element
                .removeAttr('aria-activedescendant')
                .find('.ui-menu').addBack()
                .removeClass('ui-menu ui-widget ui-widget-content ui-corner-all ui-menu-icons')
                .removeAttr('role')
                .removeAttr('tabIndex')
                .removeAttr('aria-labelledby')
                .removeAttr('aria-expanded')
                .removeAttr('aria-hidden')
                .removeAttr('aria-disabled')
                .removeUniqueId()
                .show();

            // Destroy menu items
            this.element.find('.ui-menu-item')
                .removeClass('ui-menu-item')
                .removeAttr('role')
                .removeAttr('aria-disabled')
                .children('a')
                .removeUniqueId()
                .removeClass('ui-corner-all ui-state-hover')
                .removeAttr('tabIndex')
                .removeAttr('role')
                .removeAttr('aria-haspopup')
                .children().each(function () {
                    var elem = $(this);

                    if (elem.data('ui-menu-submenu-carat')) {
                        elem.remove();
                    }
                });

            // Destroy menu dividers
            this.element.find('.ui-menu-divider').removeClass('ui-menu-divider ui-widget-content');
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _keydown: function (event) {
            var match, prev, character, skip, regex,
                preventDefault = true;

            /**
             * @param {String} value
             */
            function escape(value) {
                return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
            }

            switch (event.keyCode) {
                case $.ui.keyCode.PAGE_UP:
                    this.previousPage(event);
                    break;

                case $.ui.keyCode.PAGE_DOWN:
                    this.nextPage(event);
                    break;

                case $.ui.keyCode.HOME:
                    this._move('first', 'first', event);
                    break;

                case $.ui.keyCode.END:
                    this._move('last', 'last', event);
                    break;

                case $.ui.keyCode.UP:
                    this.previous(event);
                    break;

                case $.ui.keyCode.DOWN:
                    this.next(event);
                    break;

                case $.ui.keyCode.LEFT:
                    this.collapse(event);
                    break;

                case $.ui.keyCode.RIGHT:
                    if (this.active && !this.active.is('.ui-state-disabled')) {
                        this.expand(event);
                    }
                    break;

                case $.ui.keyCode.ENTER:
                case $.ui.keyCode.SPACE:
                    this._activate(event);
                    break;

                case $.ui.keyCode.ESCAPE:
                    this.collapse(event);
                    break;

                default:
                    preventDefault = false;
                    prev = this.previousFilter || '';
                    character = String.fromCharCode(event.keyCode);
                    skip = false;

                    clearTimeout(this.filterTimer);

                    if (character === prev) {
                        skip = true;
                    } else {
                        character = prev + character;
                    }

                    regex = new RegExp('^' + escape(character), 'i');
                    match = this.activeMenu.children('.ui-menu-item').filter(function () {
                        return regex.test($(this).children('a').text());
                    });
                    match = skip && match.index(this.active.next()) !== -1 ?
                        this.active.nextAll('.ui-menu-item') :
                        match;

                    // If no matches on the current filter, reset to the last character pressed
                    // to move down the menu to the first item that starts with that character
                    if (!match.length) {
                        character = String.fromCharCode(event.keyCode);
                        regex = new RegExp('^' + escape(character), 'i');
                        match = this.activeMenu.children('.ui-menu-item').filter(function () {
                            return regex.test($(this).children('a').text());
                        });
                    }

                    if (match.length) {
                        this.focus(event, match);

                        if (match.length > 1) { //eslint-disable-line max-depth
                            this.previousFilter = character;
                            this.filterTimer = this._delay(function () {
                                delete this.previousFilter;
                            }, 1000);
                        } else {
                            delete this.previousFilter;
                        }
                    } else {
                        delete this.previousFilter;
                    }
            }

            if (preventDefault) {
                event.preventDefault();
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _activate: function (event) {
            if (!this.active.is('.ui-state-disabled')) {
                if (this.active.children('a[aria-haspopup="true"]').length) {
                    this.expand(event);
                } else {
                    this.select(event);
                }
            }
        },

        /**
         * Refresh.
         */
        refresh: function () {
            var menus,
                icon = this.options.icons.submenu,
                submenus = this.element.find(this.options.menus);

            // Initialize nested menus
            submenus.filter(':not(.ui-menu)')
                .addClass('ui-menu ui-widget ui-widget-content ui-corner-all')
                .hide()
                .attr({
                    role: this.options.role,
                    'aria-hidden': 'true',
                    'aria-expanded': 'false'
                })
                .each(function () {
                    var menu = $(this),
                        item = menu.prev('a'),
                        submenuCarat = $('<span>')
                            .addClass('ui-menu-icon ui-icon ' + icon)
                            .data('ui-menu-submenu-carat', true);

                    item
                        .attr('aria-haspopup', 'true')
                        .prepend(submenuCarat);
                    menu.attr('aria-labelledby', item.attr('id'));
                });

            menus = submenus.add(this.element);

            // Don't refresh list items that are already adapted
            menus.children(':not(.ui-menu-item):has(a)')
                .addClass('ui-menu-item')
                .attr('role', 'presentation')
                .children('a')
                .uniqueId()
                .addClass('ui-corner-all')
                .attr({
                    tabIndex: -1,
                    role: this._itemRole()
                });

            // Initialize unlinked menu-items containing spaces and/or dashes only as dividers
            menus.children(':not(.ui-menu-item)').each(function () {
                var item = $(this);

                // hyphen, em dash, en dash
                if (!/[^\-\u2014\u2013\s]/.test(item.text())) {
                    item.addClass('ui-widget-content ui-menu-divider');
                }
            });

            // Add aria-disabled attribute to any disabled menu item
            menus.children('.ui-state-disabled').attr('aria-disabled', 'true');

            // If the active item has been removed, blur the menu
            if (this.active && !$.contains(this.element[0], this.active[0])) {
                this.blur();
            }
        },

        /**
         * @return {*}
         * @private
         */
        _itemRole: function () {
            return {
                menu: 'menuitem',
                listbox: 'option'
            }[this.options.role];
        },

        /**
         * @param {String} key
         * @param {*} value
         * @private
         */
        _setOption: function (key, value) {
            if (key === 'icons') {
                this.element.find('.ui-menu-icon')
                    .removeClass(this.options.icons.submenu)
                    .addClass(value.submenu);
            }
            this._super(key, value);
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} item
         */
        focus: function (event, item) {
            var nested, focused;

            this.blur(event, event && event.type === 'focus');

            this._scrollIntoView(item);

            this.active = item.first();
            focused = this.active.children('a').addClass('ui-state-focus');
            // Only update aria-activedescendant if there's a role
            // otherwise we assume focus is managed elsewhere
            if (this.options.role) {
                this.element.attr('aria-activedescendant', focused.attr('id'));
            }

            // Highlight active parent menu item, if any
            this.active
                .parent()
                .closest('.ui-menu-item')
                .children('a:first')
                .addClass('ui-state-active');

            if (event && event.type === 'keydown') {
                this._close();
            } else {
                this.timer = this._delay(function () {
                    this._close();
                }, this.delay);
            }

            nested = item.children('.ui-menu');

            if (nested.length && /^mouse/.test(event.type)) {
                this._startOpening(nested);
            }
            this.activeMenu = item.parent();

            this._trigger('focus', event, {
                item: item
            });
        },

        /**
         * @param {Object} item
         * @private
         */
        _scrollIntoView: function (item) {
            var borderTop, paddingTop, offset, scroll, elementHeight, itemHeight;

            if (this._hasScroll()) {
                borderTop = parseFloat($.css(this.activeMenu[0], 'borderTopWidth')) || 0;
                paddingTop = parseFloat($.css(this.activeMenu[0], 'paddingTop')) || 0;
                offset = item.offset().top - this.activeMenu.offset().top - borderTop - paddingTop;
                scroll = this.activeMenu.scrollTop();
                elementHeight = this.activeMenu.height();
                itemHeight = item.height();

                if (offset < 0) {
                    this.activeMenu.scrollTop(scroll + offset);
                } else if (offset + itemHeight > elementHeight) {
                    this.activeMenu.scrollTop(scroll + offset - elementHeight + itemHeight);
                }
            }
        },

        /**
         * @param {jQuery.Event} event
         * @param {*} fromFocus
         */
        blur: function (event, fromFocus) {
            if (!fromFocus) {
                clearTimeout(this.timer);
            }

            if (!this.active) {
                return;
            }

            this.active.children('a').removeClass('ui-state-focus');
            this.active = null;

            this._trigger('blur', event, {
                item: this.active
            });
        },

        /**
         * @param {*} submenu
         * @private
         */
        _startOpening: function (submenu) {
            clearTimeout(this.timer);

            // Don't open if already open fixes a Firefox bug that caused a .5 pixel
            // shift in the submenu position when mousing over the carat icon
            if (submenu.attr('aria-hidden') !== 'true') {
                return;
            }

            this.timer = this._delay(function () {
                this._close();
                this._open(submenu);
            }, this.delay);
        },

        /**
         * @param {*} submenu
         * @private
         */
        _open: function (submenu) {
            var position = $.extend({
                of: this.active
            }, this.options.position);

            clearTimeout(this.timer);
            this.element.find('.ui-menu').not(submenu.parents('.ui-menu'))
                .hide()
                .attr('aria-hidden', 'true');

            submenu
                .show()
                .removeAttr('aria-hidden')
                .attr('aria-expanded', 'true')
                .position(position);
        },

        /**
         * @param {jQuery.Event} event
         * @param {*} all
         */
        collapseAll: function (event, all) {
            clearTimeout(this.timer);
            this.timer = this._delay(function () {
                // If we were passed an event, look for the submenu that contains the event
                var currentMenu = all ? this.element :
                    $(event && event.target).closest(this.element.find('.ui-menu'));

                // If we found no valid submenu ancestor, use the main menu to close all sub menus anyway
                if (!currentMenu.length) {
                    currentMenu = this.element;
                }

                this._close(currentMenu);

                this.blur(event);
                this.activeMenu = currentMenu;
            }, this.delay);
        },

        // With no arguments, closes the currently active menu - if nothing is active
        // it closes all menus.  If passed an argument, it will search for menus BELOW
        /**
         * With no arguments, closes the currently active menu - if nothing is active
         * it closes all menus.  If passed an argument, it will search for menus BELOW
         * @param {*} startMenu
         * @private
         */
        _close: function (startMenu) {
            if (!startMenu) {
                startMenu = this.active ? this.active.parent() : this.element;
            }

            startMenu
                .find('.ui-menu')
                .hide()
                .attr('aria-hidden', 'true')
                .attr('aria-expanded', 'false')
                .end()
                .find('a.ui-state-active')
                .removeClass('ui-state-active');
        },

        /**
         * @param {jQuery.Event} event
         */
        collapse: function (event) {
            var newItem = this.active &&
                this.active.parent().closest('.ui-menu-item', this.element);

            if (newItem && newItem.length) {
                this._close();
                this.focus(event, newItem);
            }
        },

        /**
         * @param {jQuery.Event} event
         */
        expand: function (event) {
            var newItem = this.active &&
                this.active
                    .children('.ui-menu ')
                    .children('.ui-menu-item')
                    .first();

            if (newItem && newItem.length) {
                this._open(newItem.parent());

                // Delay so Firefox will not hide activedescendant change in expanding submenu from AT
                this._delay(function () {
                    this.focus(event, newItem);
                });
            }
        },

        /**
         * @param {jQuery.Event} event
         */
        next: function (event) {
            this._move('next', 'first', event);
        },

        /**
         * @param {jQuery.Event} event
         */
        previous: function (event) {
            this._move('prev', 'last', event);
        },

        /**
         * @return {null|*|Boolean}
         */
        isFirstItem: function () {
            return this.active && !this.active.prevAll('.ui-menu-item').length;
        },

        /**
         * @return {*|null|Boolean}
         */
        isLastItem: function () {
            return this.active && !this.active.nextAll('.ui-menu-item').length;
        },

        /**
         * @param {*} direction
         * @param {*} filter
         * @param {jQuery.Event} event
         * @private
         */
        _move: function (direction, filter, event) {
            var next;

            if (this.active) {
                if (direction === 'first' || direction === 'last') {
                    next = this.active
                        [direction === 'first' ? 'prevAll' : 'nextAll']('.ui-menu-item')
                        .eq(-1);
                } else {
                    next = this.active
                        [direction + 'All']('.ui-menu-item')
                        .eq(0);
                }
            }

            if (!next || !next.length || !this.active) {
                next = this.activeMenu.children('.ui-menu-item')[filter]();
            }

            this.focus(event, next);
        },

        /**
         * @param {jQuery.Event} event
         */
        nextPage: function (event) {
            var item, base, height;

            if (!this.active) {
                this.next(event);

                return;
            }

            if (this.isLastItem()) {
                return;
            }

            if (this._hasScroll()) {
                base = this.active.offset().top;
                height = this.element.height();
                this.active.nextAll('.ui-menu-item').each(function () {
                    item = $(this);

                    return item.offset().top - base - height < 0;
                });

                this.focus(event, item);
            } else {
                this.focus(event, this.activeMenu.children('.ui-menu-item')
                    [!this.active ? 'first' : 'last']());
            }
        },

        /**
         * @param {jQuery.Event} event
         */
        previousPage: function (event) {
            var item, base, height;

            if (!this.active) {
                this.next(event);

                return;
            }

            if (this.isFirstItem()) {
                return;
            }

            if (this._hasScroll()) {
                base = this.active.offset().top;
                height = this.element.height();
                this.active.prevAll('.ui-menu-item').each(function () {
                    item = $(this);

                    return item.offset().top - base + height > 0;
                });

                this.focus(event, item);
            } else {
                this.focus(event, this.activeMenu.children('.ui-menu-item').first());
            }
        },

        /**
         * @return {Boolean}
         * @private
         */
        _hasScroll: function () {
            return this.element.outerHeight() < this.element.prop('scrollHeight');
        },

        /**
         * @param {jQuery.Event} event
         */
        select: function (event) {
            // TODO: It should never be possible to not have an active item at this
            // point, but the tests don't trigger mouseenter before click.
            var ui;

            this.active = this.active || $(event.target).closest('.ui-menu-item');
            ui = {
                item: this.active
            };

            if (!this.active.has('.ui-menu').length) {
                this.collapseAll(event, true);
            }
            this._trigger('select', event, ui);
        }
    });

    return $.mage.menu;
}));

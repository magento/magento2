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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

;
(function($) {
    'use strict';

    $.widget('mage.globalSearch', {
        options: {
            header: '.header',
            headerActiveClass: 'active',
            form: '#form-search',
            input: 'input',
            inputDefaultWidth: 50,
            inputOpenedWidth: 350,
            submitButton: 'button[type="submit"]',
            timeoutId: null,
            actionSpeed: 500
        },

        _create: function() {
            this.header = $(this.options.header);
            this.form = $(this.options.form);
            this.input = $(this.options.input, this.form);
            this.submitButton = $(this.options.submitButton, this.form);

            this._events();
        },

        _events: function() {
            var self = this;

            this.form
                .on('submit.submitGlobalSearchRequest', function() {
                    if (!self.input.val()) {
                        self.header.addClass(self.options.headerActiveClass);
                        self.input
                            .animate({
                                width: self.options.inputOpenedWidth
                            }, self.options.actionSpeed)
                            .focus();
                    } else {
                        this.submit();
                    }

                    return false;
                });

            this.input
                .on('blur.resetGlobalSearchForm', function() {
                    if (!self.input.val()) {
                        self.timeoutId && clearTimeout(self.timeoutId);
                        self.timeoutId = setTimeout(function() {
                            self.input
                                .animate({
                                    width: self.options.inputDefaultWidth
                                }, 200, function() {
                                    var callbackTimeout = setTimeout(function() {
                                        self.header.removeClass(self.options.headerActiveClass);
                                    }, self.options.actionSpeed);
                                });
                        }, self.options.actionSpeed);
                    }
                });

            this.submitButton
                .on('click.activateGlobalSearch', function() {
                    self.timeoutId && clearTimeout(self.timeoutId);
                });
        }
    });

    $.widget('mage.globalNavigation', {
        options: {
            menuCategory: '.level-0.parent',
            menuLinks: 'a'
        },

        _create: function() {
            this.menu = this.element;
            this.menuCategory = $(this.options.menuCategory, this.menu);
            this.menuLinks = $(this.options.menuLinks, this.menuCategory);

            this._events();
        },

        _events: function() {
            var self = this;

            var config = {
                interval: 100,
                over: self._hoverEffects, // function = onMouseOver callback (REQUIRED)
                timeout: 700, // number = milliseconds delay before onMouseOut
                out: self._leaveEffects // function = onMouseOut callback (REQUIRED)
            };

            this.menuCategory
                .hoverIntent(config)
                .on('hover', function() {
                    $(this)
                    .addClass('recent')
                    .siblings('.level-0')
                    .removeClass('recent');
/*                    $(this)
                        .siblings('.level-0')
                            .removeClass('hover')
                            .find('> .submenu')
                                .hide();*/
                });

            this.menuLinks
                .on('focus.tabFocus', function() {
                    $(this).closest('.level-0.parent')
                        .trigger('mouseenter');
                })
                .on('blur.tabFocus', function() {
                    $(this).closest('.level-0.parent')
                        .trigger('mouseleave');
                });
        },

        _hoverEffects: function () {
            var availableWidth = parseInt($(this).parent().css('width')) - $(this).position().left,
                submenu = $('> .submenu', this),
                colsWidth = 0;

            $(this)
                .addClass('hover')
/*                .siblings('.level-0.parent')
                .find('> .submenu').hide()*/
                ;

            submenu.show();

            $.each($('> .submenu > ul li.column', this), function() {
                colsWidth = colsWidth + parseInt($(this).css('width'));
            });

            var containerPaddings =  parseInt(submenu.css('padding-left')) + parseInt(submenu.css('padding-right'));

            $(this).toggleClass('reverse', (containerPaddings + colsWidth) > availableWidth);

            submenu
                .hide()
                .slideDown('fast');
        },

        _leaveEffects: function () {
            var self = $(this);

            $('> .submenu', this)
                .slideUp('fast', function() {
                    self.removeClass('hover');
                });
        }
    });

    $.widget('mage.modalPopup', {
        options: {
            popup: '.popup',
            btnClose: '[data-dismiss="popup"]'
        },

        _create: function() {
            this.fade = this.element;
            this.popup = $(this.options.popup, this.fade);
            this.btnClose = $(this.options.btnClose, this.popup);

            this._events();
        },

        _events: function() {
            var self = this;

            this.btnClose
                .on('click.closeModalPopup', function() {
                    self.fade.remove();
                });
        }
    });

    $.widget('mage.loadingPopup', {
        options: {
            message: 'Please wait...',
            timeout: 5000,
            timeoutId: null,
            callback: null,
            template: null
        },

        _create: function() {
            this.template =
                '<div class="popup popup-loading">' +
                    '<div class="popup-inner">' + this.options.message + '</div>' +
                '</div>';

            this.popup = $(this.template);

            this._show();
            this._events();
        },

        _events: function() {
            var self = this;

            this.element
                .on('showLoadingPopup', function() {
                    self._show();
                })
                .on('hideLoadingPopup', function() {
                    self._hide();
                });
        },

        _show: function() {
            var self = this;

            this.element.append(this.popup);

            if (this.options.timeout) {
                this.options.timeoutId = setTimeout(function() {
                    self._hide();

                    self.options.callback && self.options.callback();

                    self.options.timeoutId && clearTimeout(self.options.timeoutId);
                }, self.options.timeout);
            }
        },

        _hide: function() {
            this.popup.remove();
            this.destroy();
        }
    });

    $(document).ready(function() {
        $('.header-panel .search').globalSearch();
        $('.navigation').globalNavigation();
        $('.fade').modalPopup();
        $('details').details();

        /* Listen events on "Collapsable" events */
        $('.collapse')
            .on('show', function () {
                var fieldsetWrapper = $(this).closest('.fieldset-wrapper');

                fieldsetWrapper.addClass('opened');
            })
            .on('hide', function () {
                var fieldsetWrapper = $(this).closest('.fieldset-wrapper');

                fieldsetWrapper.removeClass('opened');
            });

        $.each($('.entry-edit'), function(i, entry) {
            $('.collapse:first', entry).collapse('show');
        });


        /* Switcher for IE8 */
        if ($.browser.msie && $.browser.version == '8.0') {
            var checkboxSwitcher = $('.switcher input');

            var toggleCheckboxState = function(elem) {
                elem.toggleClass('checked', elem.prop('checked'));
            };
            toggleCheckboxState(checkboxSwitcher);

            $('.switcher')
                .on('change.toggleSwitcher', function() {
                    toggleCheckboxState(checkboxSwitcher);
                });
        }

        $('.page-actions').floatingHeader();

    });
})(window.jQuery);
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define('globalNavigation', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.globalNavigation', {
        options: {
            selectors: {
                topLevelItem: '.level-0',
                topLevelHref: '> a',
                subMenu: '> .submenu',
                closeSubmenuBtn: '[data-role="close-submenu"]'
            },
            overlayTmpl: '<div class="admin__menu-overlay"></div>'
        },

        _create: function () {
            var selectors = this.options.selectors;

            this.menu      = this.element;
            this.menuLinks = $(selectors.topLevelHref, selectors.topLevelItem);

            this._initOverlay()
                ._bind();
        },

        _initOverlay: function () {
            var wrapper = $('<div />').addClass('admin__scope');

            this.overlay = $(this.options.overlayTmpl).appendTo('body').hide(0);

            /**
             * @todo fix LESS and remove next line and wrapper definition
             */
            this.overlay.wrap(wrapper);

            return this;
        },

        _bind: function () {
            var lighten = this._lighten.bind(this),
                open    = this._open.bind(this),
                darken  = this._darken.bind(this);

            this.menuLinks
                .on('focus', lighten)
                .on('click', open)
                .on('blur',  darken);
        },

        _lighten: function (e) {
            var selectors = this.options.selectors,
                menuItem  = $(e.target).closest(selectors.topLevelItem);

            menuItem
                .addClass('_active')
                .siblings(selectors.topLevelItem)
                .removeClass('_active');
        },

        _darken: function (e) {
            var selectors = this.options.selectors,
                menuItem  = $(e.target).closest(selectors.topLevelItem);

            menuItem.removeClass('_active');
        },

        _closeSubmenu: function (e) {
            var selectors = this.options.selectors,
                menuItem  = $(e.target).closest(selectors.topLevelItem);

            this._close(e);

            $(selectors.topLevelHref, menuItem).focus();
        },

        _open: function (e) {
            var selectors           = this.options.selectors,
                menuItemSelector    = selectors.topLevelItem,
                menuItem            = $(e.target).closest(menuItemSelector),
                subMenu             = $(selectors.subMenu, menuItem),
                close               = this._closeSubmenu.bind(this),
                closeBtn            = subMenu.find(selectors.closeSubmenuBtn);

            if (subMenu.length) {
                e.preventDefault();
            }

            menuItem
                .addClass('_hover _recent')
                .siblings(menuItemSelector)
                .removeClass('_hover _recent');

            subMenu.attr('aria-expanded', 'true');

            closeBtn.on('click', close);

            this.overlay.show(0).on('click', close);
        },

        _close: function (e) {
            var selectors   = this.options.selectors,
                menuItem    = this.menu.find(selectors.topLevelItem + '._hover._recent'),
                subMenu     = $(selectors.subMenu, menuItem),
                closeBtn    = subMenu.find(selectors.closeSubmenuBtn);

            e.preventDefault();

            this.overlay.hide(0).off('click');

            closeBtn.off('click');

            subMenu.attr('aria-expanded', 'false');

            menuItem.removeClass('_hover _recent');
        }
    });

    return $.mage.globalNavigation;
});

define('globalSearch', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.globalSearch', {
        options: {
            field: '.search-global-field',
            fieldActiveClass: '_active',
            input: '#search-global'
        },

        _create: function () {
            this.field = $(this.options.field);
            this.input = $(this.options.input);
            this._events();
        },

        _events: function () {
            var self = this;

            this.input
                .on('blur.resetGlobalSearchForm', function () {
                    if (!self.input.val()) {
                        self.field.removeClass(self.options.fieldActiveClass)
                    }
                });

            this.input
                .on('focus.activateGlobalSearchForm', function () {
                    self.field.addClass(self.options.fieldActiveClass)
                });
        }
    });

    return $.mage.globalSearch;
});

define('modalPopup', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.modalPopup', {
        options: {
            popup: '.popup',
            btnDismiss: '[data-dismiss="popup"]',
            btnHide: '[data-hide="popup"]'
        },

        _create: function () {
            this.fade = this.element;
            this.popup = $(this.options.popup, this.fade);
            this.btnDismiss = $(this.options.btnDismiss, this.popup);
            this.btnHide = $(this.options.btnHide, this.popup);

            this._events();
        },

        _events: function () {
            var self = this;

            this.btnDismiss
                .on('click.dismissModalPopup', function () {
                    self.fade.remove();
                });

            this.btnHide
                .on('click.hideModalPopup', function () {
                    self.fade.hide();
                });
        }
    });

    return $.mage.modalPopup;
});

define('useDefault', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.useDefault', {
        options: {
            field: '.field',
            useDefault: '.use-default',
            checkbox: '.use-default-control',
            label: '.use-default-label'
        },

        _create: function () {
            this.el = this.element;
            this.field = $(this.el).closest(this.options.field);
            this.useDefault = $(this.options.useDefault, this.field);
            this.checkbox = $(this.options.checkbox, this.useDefault);
            this.label = $(this.options.label, this.useDefault);
            this.origValue = this.el.attr('data-store-label');

            this._events();
        },

        _events: function () {
            var self = this;

            this.el
                .on('change.toggleUseDefaultVisibility keyup.toggleUseDefaultVisibility', $.proxy(this._toggleUseDefaultVisibility, this))
                .trigger('change.toggleUseDefaultVisibility');

            this.checkbox
                .on('change.setOrigValue', function () {
                    if ($(this).prop('checked')) {
                        self.el
                            .val(self.origValue)
                            .trigger('change.toggleUseDefaultVisibility');

                        $(this).prop('checked', false);
                    }
                });
        },

        _toggleUseDefaultVisibility: function () {
            var curValue = this.el.val(),
                origValue = this.origValue;

            this[curValue != origValue ? '_show' : '_hide']();
        },

        _show: function () {
            this.useDefault.show();
        },

        _hide: function () {
            this.useDefault.hide();
        }
    });

    return $.mage.useDefault;
});

define('loadingPopup', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.loadingPopup', {
        options: {
            message: 'Please wait...',
            timeout: 5000,
            timeoutId: null,
            callback: null,
            template: null
        },

        _create: function () {
            this.template =
                '<div class="popup popup-loading">' +
                '<div class="popup-inner">' + this.options.message + '</div>' +
                '</div>';

            this.popup = $(this.template);

            this._show();
            this._events();
        },

        _events: function () {
            var self = this;

            this.element
                .on('showLoadingPopup', function () {
                    self._show();
                })
                .on('hideLoadingPopup', function () {
                    self._hide();
                });
        },

        _show: function () {
            var options = this.options,
                timeout = options.timeout;

            $('body').trigger('processStart');

            if (timeout) {
                options.timeoutId = setTimeout(this._delayedHide.bind(this), timeout);
            }
        },

        _hide: function () {
            $('body').trigger('processStop');
        },

        _delayedHide: function () {
            this._hide();

            this.options.callback && this.options.callback();

            this.options.timeoutId && clearTimeout(this.options.timeoutId);
        }
    });

    return $.mage.loadingPopup;
});

define('collapsable', [
    'jquery',
    'jquery/ui',
    'jquery/jquery.tabs'
], function ($) {
    'use strict';

    $.widget('mage.collapsable', {
        options: {
            parent: null,
            openedClass: 'opened',
            wrapper: '.fieldset-wrapper'
        },

        _create: function () {
            this._events();
        },

        _events: function () {
            var self = this;

            this.element
                .on('show', function (e) {
                    var fieldsetWrapper = $(this).closest(self.options.wrapper);

                    fieldsetWrapper.addClass(self.options.openedClass);
                    e.stopPropagation();
                })
                .on('hide', function (e) {
                    var fieldsetWrapper = $(this).closest(self.options.wrapper);

                    fieldsetWrapper.removeClass(self.options.openedClass);
                    e.stopPropagation();
                });
        }
    });

    return $.mage.collapsable;
});

define('js/theme', [
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/ie-class-fixer',
    'collapsable',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    /* @TODO refactor collapsable as widget and avoid logic binding with such a general selectors */
    $('.collapse').collapsable();

    $.each($('.entry-edit'), function (i, entry) {
        $('.collapse:first', entry).filter(function () {
            return $(this).data('collapsed') !== true;
        }).collapse('show');
    });

    keyboardHandler.apply();
});

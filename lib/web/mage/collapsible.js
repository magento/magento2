/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui',
    'jquery/jquery-storageapi',
    'mage/mage'
], function ($) {
    'use strict';

    var hideProps = {},
        showProps = {};

    hideProps.height =  'hide';
    showProps.height =  'show';

    $.widget('mage.collapsible', {
        options: {
            active: false,
            disabled: false,
            collapsible: true,
            header: '[data-role=title]',
            content: '[data-role=content]',
            trigger: '[data-role=trigger]',
            closedState: null,
            openedState: null,
            disabledState: null,
            ajaxUrlElement: '[data-ajax=true]',
            ajaxContent: false,
            loadingClass: null,
            saveState: false,
            animate: false,
            icons: {
                activeHeader: null,
                header: null
            },
            collateral: {
                element: null,
                openedState: null
            }
        },

        /**
         * @private
         */
        _create: function () {
            this.storage = $.localStorage;
            this.icons = false;

            if (typeof this.options.icons === 'string') {
                this.options.icons = $.parseJSON(this.options.icons);
            }

            this._processPanels();
            this._processState();
            this._refresh();

            if (this.options.icons.header && this.options.icons.activeHeader) {
                this._createIcons();
                this.icons = true;
            }

            this._bind('click');
            this._trigger('created');
        },

        /**
         * @private
         */
        _refresh: function () {
            this.trigger.attr('tabIndex', 0);

            if (this.options.active && !this.options.disabled) {
                if (this.options.openedState) {
                    this.element.addClass(this.options.openedState);
                }

                if (this.options.collateral.element && this.options.collateral.openedState) {
                    $(this.options.collateral.element).addClass(this.options.collateral.openedState);
                }

                if (this.options.ajaxContent) {
                    this._loadContent();
                }
                // ARIA (updates aria attributes)
                this.header.attr({
                    'aria-selected': false
                });
            } else if (this.options.disabled) {
                this.disable();
            } else {
                this.content.hide();

                if (this.options.closedState) {
                    this.element.addClass(this.options.closedState);
                }
            }
        },

        /**
         * Processing the state:
         *     If deep linking is used and the anchor is the id of the content or the content contains this id,
         *     and the collapsible element is a nested one having collapsible parents, in order to see the content,
         *     all the parents must be expanded.
         * @private
         */
        _processState: function () {
            var anchor = window.location.hash,
                isValid = $.mage.isValidSelector(anchor),
                urlPath = window.location.pathname.replace(/\./g, ''),
                state;

            this.stateKey = encodeURIComponent(urlPath + this.element.attr('id'));

            if (isValid &&
                ($(this.content.find(anchor)).length > 0 || this.content.attr('id') === anchor.replace('#', ''))
            ) {
                this.element.parents('[data-collapsible=true]').collapsible('forceActivate');

                if (!this.options.disabled) {
                    this.options.active = true;

                    if (this.options.saveState) { //eslint-disable-line max-depth
                        this.storage.set(this.stateKey, true);
                    }
                }
            } else if (this.options.saveState && !this.options.disabled) {
                state = this.storage.get(this.stateKey);

                if (typeof state === 'undefined' || state === null) {
                    this.storage.set(this.stateKey, this.options.active);
                } else if (state === true) {
                    this.options.active = true;
                } else if (state === false) {
                    this.options.active = false;
                }
            }
        },

        /**
         * @private
         */
        _createIcons: function () {
            var icons = this.options.icons;

            if (icons) {
                $('<span>')
                    .addClass(icons.header)
                    .attr('data-role', 'icons')
                    .prependTo(this.header);

                if (this.options.active && !this.options.disabled) {
                    this.header.children('[data-role=icons]')
                        .removeClass(icons.header)
                        .addClass(icons.activeHeader);
                }
            }
        },

        /**
         * @private
         */
        _destroyIcons: function () {
            this.header
                .children('[data-role=icons]')
                .remove();
        },

        /**
         * @private
         */
        _destroy: function () {
            var options = this.options;

            this.element.removeAttr('data-collapsible');

            this.trigger.removeAttr('tabIndex');

            if (options.openedState) {
                this.element.removeClass(options.openedState);
            }

            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).removeClass(this.options.collateral.openedState);
            }

            if (options.closedState) {
                this.element.removeClass(options.closedState);
            }

            if (options.disabledState) {
                this.element.removeClass(options.disabledState);
            }

            if (this.icons) {
                this._destroyIcons();
            }
        },

        /**
         * @private
         */
        _processPanels: function () {
            var headers, triggers;

            this.element.attr('data-collapsible', 'true');

            if (typeof this.options.header === 'object') {
                this.header = this.options.header;
            } else {
                headers = this.element.find(this.options.header);

                if (headers.length > 0) {
                    this.header = headers.eq(0);
                } else {
                    this.header = this.element;
                }
            }

            if (typeof this.options.content === 'object') {
                this.content = this.options.content;
            } else {
                this.content = this.header.next(this.options.content).eq(0);
            }

            // ARIA (init aria attributes)
            if (this.header.attr('id')) {
                this.content.attr('aria-labelledby', this.header.attr('id'));
            }

            if (this.content.attr('id')) {
                this.header.attr('aria-controls', this.content.attr('id'));
            }

            this.header
                .attr({
                    'role': 'tab',
                    'aria-selected': this.options.active,
                    'aria-expanded': this.options.active
                });

            // For collapsible widget only (not tabs or accordion)
            if (this.header.parent().attr('role') !== 'presentation') {
                this.header
                    .parent()
                    .attr('role', 'tablist');
            }

            this.content.attr({
                'role': 'tabpanel',
                'aria-hidden': !this.options.active
            });

            if (typeof this.options.trigger === 'object') {
                this.trigger = this.options.trigger;
            } else {
                triggers = this.header.find(this.options.trigger);

                if (triggers.length > 0) {
                    this.trigger = triggers.eq(0);
                } else {
                    this.trigger = this.header;
                }
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _keydown: function (event) {
            var keyCode;

            if (event.altKey || event.ctrlKey) {
                return;
            }

            keyCode = $.ui.keyCode;

            switch (event.keyCode) {
                case keyCode.SPACE:
                case keyCode.ENTER:
                    this._eventHandler(event);
                    break;
            }

        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _bind: function (event) {
            var self = this;

            this.events = {
                keydown: '_keydown'
            };

            if (event) {
                $.each(event.split(' '), function (index, eventName) {
                    self.events[ eventName ] = '_eventHandler';
                });
            }
            this._off(this.trigger);

            if (!this.options.disabled) {
                this._on(this.trigger, this.events);
            }
        },

        /**
         * Disable.
         */
        disable: function () {
            this.options.disabled = true;
            this._off(this.trigger);
            this.forceDeactivate();

            if (this.options.disabledState) {
                this.element.addClass(this.options.disabledState);
            }
            this.trigger.attr('tabIndex', -1);
        },

        /**
         * Enable.
         */
        enable: function () {
            this.options.disabled = false;
            this._on(this.trigger, this.events);
            this.forceActivate();

            if (this.options.disabledState) {
                this.element.removeClass(this.options.disabledState);
            }
            this.trigger.attr('tabIndex', 0);
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _eventHandler: function (event) {

            if (this.options.active && this.options.collapsible) {
                this.deactivate();
            } else {
                this.activate();

            }
            event.preventDefault();

        },

        /**
         * @param {*} prop
         * @private
         */
        _animate: function (prop) {
            var duration,
                easing,
                animate = this.options.animate;

            if (typeof animate === 'number') {
                duration = animate;
            }

            if (typeof animate === 'string') {
                animate = $.parseJSON(animate);
            }
            duration = duration || animate.duration;
            easing = animate.easing;
            this.content.animate(prop, duration, easing);
        },

        /**
         * Deactivate.
         */
        deactivate: function () {
            if (this.options.animate) {
                this._animate(hideProps);
            } else {
                this.content.hide();
            }
            this._close();
        },

        /**
         * Force deactivate.
         */
        forceDeactivate: function () {
            this.content.hide();
            this._close();

        },

        /**
         * @private
         */
        _close: function () {
            this.options.active = false;

            if (this.options.saveState) {
                this.storage.set(this.stateKey, false);
            }

            if (this.options.openedState) {
                this.element.removeClass(this.options.openedState);
            }

            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).removeClass(this.options.collateral.openedState);
            }

            if (this.options.closedState) {
                this.element.addClass(this.options.closedState);
            }

            if (this.icons) {
                this.header.children('[data-role=icons]')
                    .removeClass(this.options.icons.activeHeader)
                    .addClass(this.options.icons.header);
            }

            // ARIA (updates aria attributes)
            this.header.attr({
                'aria-selected': 'false',
                'aria-expanded': 'false'
            });
            this.content.attr({
                'aria-hidden': 'true'
            });

            this.element.trigger('dimensionsChanged', {
                opened: false
            });
        },

        /**
         * Activate.
         */
        activate: function () {
            if (!this.options.disabled) {
                if (this.options.animate) {
                    this._animate(showProps);
                } else {
                    this.content.show();
                }
                this._open();
            }
        },

        /**
         * Force activate.
         */
        forceActivate: function () {
            if (!this.options.disabled) {
                this.content.show();
                this._open();
            }
        },

        /**
         * @private
         */
        _open: function () {
            this.element.trigger('beforeOpen');
            this.options.active = true;

            if (this.options.ajaxContent) {
                this._loadContent();
            }

            if (this.options.saveState) {
                this.storage.set(this.stateKey, true);
            }

            if (this.options.openedState) {
                this.element.addClass(this.options.openedState);
            }

            if (this.options.collateral.element && this.options.collateral.openedState) {
                $(this.options.collateral.element).addClass(this.options.collateral.openedState);
            }

            if (this.options.closedState) {
                this.element.removeClass(this.options.closedState);
            }

            if (this.icons) {
                this.header.children('[data-role=icons]')
                    .removeClass(this.options.icons.header)
                    .addClass(this.options.icons.activeHeader);
            }

            // ARIA (updates aria attributes)
            this.header.attr({
                'aria-selected': 'true',
                'aria-expanded': 'true'
            });
            this.content.attr({
                'aria-hidden': 'false'
            });

            this.element.trigger('dimensionsChanged', {
                opened: true
            });
        },

        /**
         * @private
         */
        _loadContent: function () {
            var url = this.element.find(this.options.ajaxUrlElement).attr('href'),
                that = this;

            if (url) {
                that.xhr = $.get({
                    url: url,
                    dataType: 'html'
                }, function () {
                });
            }

            if (that.xhr && that.xhr.statusText !== 'canceled') {
                if (that.options.loadingClass) {
                    that.element.addClass(that.options.loadingClass);
                }
                that.content.attr('aria-busy', 'true');
                that.xhr.success(function (response) {
                    setTimeout(function () {
                        that.content.html(response);
                    }, 1);
                });
                that.xhr.complete(function (jqXHR, status) {
                    setTimeout(function () {
                        if (status === 'abort') {
                            that.content.stop(false, true);
                        }

                        if (that.options.loadingClass) {
                            that.element.removeClass(that.options.loadingClass);
                        }
                        that.content.removeAttr('aria-busy');

                        if (jqXHR === that.xhr) {
                            delete that.xhr;
                        }
                    }, 1);
                });
            }
        }
    });

    return $.mage.collapsible;
});

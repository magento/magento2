/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
    'mage/mage',
    'mage/collapsible'
], function ($) {
    'use strict';

    $.widget('mage.tabs', {
        options: {
            active: 0,
            disabled: [],
            openOnFocus: true,
            collapsible: false,
            collapsibleElement: '[data-role=collapsible]',
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
            }
        },

        /**
         * @private
         */
        _create: function () {
            if (typeof this.options.disabled === 'string') {
                this.options.disabled = this.options.disabled.split(' ').map(function (item) {
                    return parseInt(item, 10);
                });
            }
            this._processPanels();
            this._handleDeepLinking();
            this._processTabIndex();
            this._closeOthers();
            this._bind();
        },

        /**
         * @private
         */
        _destroy: function () {
            $.each(this.collapsibles, function () {
                $(this).collapsible('destroy');
            });
        },

        /**
         * If deep linking is used, all sections must be closed but the one that contains the anchor.
         * @private
         */
        _handleDeepLinking: function () {
            var self = this,
                anchor = window.location.hash,
                isValid = $.mage.isValidSelector(anchor),
                anchorId = anchor.replace('#', '');

            if (anchor && isValid) {
                $.each(self.contents, function (i) {
                    if ($(this).attr('id') === anchorId || $(this).find('#' + anchorId).length) {
                        self.collapsibles.not(self.collapsibles.eq(i)).collapsible('forceDeactivate');

                        return false;
                    }
                });
            }
        },

        /**
         * When the widget gets instantiated, the first tab that is not disabled receive focusable property
         * All tabs receive tabIndex 0
         * @private
         */
        _processTabIndex: function () {
            var self = this;

            self.triggers.attr('tabIndex', 0);
            $.each(this.collapsibles, function (i) {
                self.triggers.attr('tabIndex', 0);
                self.triggers.eq(i).attr('tabIndex', 0);
            });
        },

        /**
         * Prepare the elements for instantiating the collapsible widget
         * @private
         */
        _processPanels: function () {
            var isNotNested = this._isNotNested.bind(this);

            this.contents = this.element
                .find(this.options.content)
                .filter(isNotNested);

            this.collapsibles =  this.element
                .find(this.options.collapsibleElement)
                .filter(isNotNested);

            this.collapsibles
                .attr('role', 'presentation')
                .parent()
                .attr('role', 'tablist');

            this.headers = this.element
                .find(this.options.header)
                .filter(isNotNested);

            if (this.headers.length === 0) {
                this.headers = this.collapsibles;
            }
            this.triggers = this.element
                .find(this.options.trigger)
                .filter(isNotNested);

            if (this.triggers.length === 0) {
                this.triggers = this.headers;
            }
            this._callCollapsible();
        },

        /**
         * Checks if element is not in nested container to keep the correct scope of collapsible
         * @param {Number} index
         * @param {HTMLElement} element
         * @private
         * @return {Boolean}
         */
        _isNotNested: function (index, element) {
            var parentContent = $(element).parents(this.options.content);

            return !parentContent.length || !this.element.find(parentContent).length;
        },

        /**
         * Setting the disabled and active tabs and calling instantiation of collapsible
         * @private
         */
        _callCollapsible: function () {
            var self = this,
                disabled = false,
                active = false;

            $.each(this.collapsibles, function (i) {
                disabled = active = false;

                if ($.inArray(i, self.options.disabled) !== -1) {
                    disabled = true;
                }

                if (i === self.options.active) {
                    active = true;
                }
                self._instantiateCollapsible(this, i, active, disabled);
            });
        },

        /**
         * Instantiate collapsible.
         *
         * @param {HTMLElement} element
         * @param {Number} index
         * @param {*} active
         * @param {*} disabled
         * @private
         */
        _instantiateCollapsible: function (element, index, active, disabled) {
            $(element).collapsible(
                $.extend({}, this.options, {
                    active: active,
                    disabled: disabled,
                    header: this.headers.eq(index),
                    content: this.contents.eq(index),
                    trigger: this.triggers.eq(index)
                })
            );
        },

        /**
         * Adding callback to close others tabs when one gets opened
         * @private
         */
        _closeOthers: function () {
            var self = this;

            $.each(this.collapsibles, function () {
                $(this).on('beforeOpen', function () {
                    self.collapsibles.not(this).collapsible('forceDeactivate');
                });
            });
        },

        /**
         * @param {*} index
         */
        activate: function (index) {
            this._toggleActivate('activate', index);
        },

        /**
         * @param {*} index
         */
        deactivate: function (index) {
            this._toggleActivate('deactivate', index);
        },

        /**
         * @param {*} action
         * @param {*} index
         * @private
         */
        _toggleActivate: function (action, index) {
            this.collapsibles.eq(index).collapsible(action);
        },

        /**
         * @param {*} index
         */
        disable: function (index) {
            this._toggleEnable('disable', index);
        },

        /**
         * @param {*} index
         */
        enable: function (index) {
            this._toggleEnable('enable', index);
        },

        /**
         * @param {*} action
         * @param {*} index
         * @private
         */
        _toggleEnable: function (action, index) {
            var self = this;

            if ($.isArray(index)) {
                $.each(index, function () {
                    self.collapsibles.eq(this).collapsible(action);
                });
            } else if (index === undefined) {
                this.collapsibles.collapsible(action);
            } else {
                this.collapsibles.eq(index).collapsible(action);
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _keydown: function (event) {
            var self = this,
                keyCode, toFocus, toFocusIndex, enabledTriggers, length, currentIndex, nextToFocus;

            if (event.altKey || event.ctrlKey) {
                return;
            }
            keyCode = $.ui.keyCode;
            toFocus = false;
            enabledTriggers = [];

            $.each(this.triggers, function () {
                if (!self.collapsibles.eq(self.triggers.index($(this))).collapsible('option', 'disabled')) {
                    enabledTriggers.push(this);
                }
            });
            length = $(enabledTriggers).length;
            currentIndex = $(enabledTriggers).index(event.target);

            /**
             * @param {String} direction
             * @return {*}
             */
            nextToFocus = function (direction) {
                if (length > 0) {
                    if (direction === 'right') {
                        toFocusIndex = (currentIndex + 1) % length;
                    } else {
                        toFocusIndex = (currentIndex + length - 1) % length;
                    }

                    return enabledTriggers[toFocusIndex];
                }

                return event.target;
            };

            switch (event.keyCode) {
                case keyCode.RIGHT:
                case keyCode.DOWN:
                    toFocus = nextToFocus('right');
                    break;

                case keyCode.LEFT:
                case keyCode.UP:
                    toFocus = nextToFocus('left');
                    break;

                case keyCode.HOME:
                    toFocus = enabledTriggers[0];
                    break;

                case keyCode.END:
                    toFocus = enabledTriggers[length - 1];
                    break;
            }

            if (toFocus) {
                toFocusIndex = this.triggers.index(toFocus);
                $(event.target).attr('tabIndex', -1);
                $(toFocus).attr('tabIndex', 0);
                toFocus.focus();

                if (this.options.openOnFocus) {
                    this.activate(toFocusIndex);
                }
                event.preventDefault();
            }
        },

        /**
         * @private
         */
        _bind: function () {
            var events = {
                keydown: '_keydown'
            };

            this._off(this.triggers);
            this._on(this.triggers, events);
        }
    });

    return $.mage.tabs;
});

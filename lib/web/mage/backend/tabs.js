/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY */
define([
    'jquery',
    'jquery/ui',
    'jquery/ui-modules/widgets/tabs'
], function ($) {
    'use strict';

    var rhash, isLocal;

    // mage.tabs base functionality
    $.widget('mage.tabs', $.ui.tabs, {
        options: {
            spinner: false,
            groups: null,
            tabPanelClass: '',
            excludedPanel: ''
        },

        /**
         * Tabs creation
         * @protected
         */
        _create: function () {
            var activeIndex = this._getTabIndex(this.options.active);

            this.options.active = activeIndex >= 0 ? activeIndex : 0;
            this._super();
        },

        /**
         * @override
         * @private
         * @return {Array} Array of DOM-elements
         */
        _getList: function () {
            if (this.options.groups) {
                return this.element.find(this.options.groups);
            }

            return this._super();
        },

        /**
         * Get active anchor
         * @return {Element}
         */
        activeAnchor: function () {
            return this.anchors.eq(this.option('active'));
        },

        /**
         * Get tab index by tab id
         * @protected
         * @param {String} id - id of tab
         * @return {Number}
         */
        _getTabIndex: function (id) {
            var anchors = this.anchors ?
                this.anchors :
                this._getList().find('> li > a[href]');

            return anchors.index($('#' + id));
        },

        /**
         * Switch between tabs
         * @protected
         * @param {Object} event - event object
         * @param {undefined|Object} eventData
         */
        _toggle: function (event, eventData) {
            var anchor = $(eventData.newTab).find('a');

            if ($(eventData.newTab).find('a').data().tabType === 'link') {
                location.href = anchor.prop('href');
            } else {
                this._superApply(arguments);
            }
        }
    });

    rhash = /#.*$/;

    /**
     * @param {*} anchor
     * @return {Boolean}
     */
    isLocal = function (anchor) {
        return anchor.hash.length > 1 &&
            anchor.href.replace(rhash, '') ===
                location.href.replace(rhash, '')
                    // support: Safari 5.1
                    // Safari 5.1 doesn't encode spaces in window.location
                    // but it does encode spaces from anchors (#8777)
                    .replace(/\s/g, '%20');
    };

    // Extension for mage.tabs - Move panels in destination element
    $.widget('mage.tabs', $.mage.tabs, {
        /**
         * Move panels in destination element on creation
         * @protected
         * @override
         */
        _create: function () {
            this._super();
            this._movePanelsInDestination(this.panels);
        },

        /**
         * Get panel for tab. If panel no exist in tabs container, then find panel in destination element
         * @protected
         * @override
         * @param {Element} tab - tab "li" DOM-element
         * @return {Element}
         */
        _getPanelForTab: function (tab) {
            var panel = this._superApply(arguments),
                id;

            if (!panel.length) {
                id = $(tab).attr('aria-controls');
                panel = $(this.options.destination).find(this._sanitizeSelector('#' + id));
            }

            return panel;
        },

        /**
         * @private
         */
        _processTabs: function () {
            var that = this;

            this.tablist = this._getList()
                .addClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all')
                .attr('role', 'tablist');

            this.tabs = this.tablist.find('> li:has(a[href])')
                .addClass('ui-state-default ui-corner-top')
                .attr({
                    role: 'tab',
                    tabIndex: -1
                });

            this.anchors = this.tabs.map(function () {
                return $('a', this)[ 0 ];
            })
                .addClass('ui-tabs-anchor')
                .attr({
                    role: 'presentation',
                    tabIndex: -1
                });

            this.panels = $();

            this.anchors.each(function (i, anchor) {
                var selector, panel, panelId,
                    anchorId = $(anchor).uniqueId().attr('id'),
                    tab = $(anchor).closest('li'),
                    originalAriaControls = tab.attr('aria-controls');

                // inline tab
                if (isLocal(anchor)) {
                    selector = anchor.hash;
                    panel = that.document.find(that._sanitizeSelector(selector));
                    // remote tab
                } else {
                    panelId = tab.attr('aria-controls') || $({}).uniqueId()[ 0 ].id;
                    selector = '#' + panelId;
                    panel = that.element.find(selector);

                    if (!panel.length) {
                        panel = that._createPanel(panelId);
                        panel.insertAfter(that.panels[ i - 1 ] || that.tablist);
                    }
                    panel.attr('aria-live', 'polite');
                }

                if (panel.length) {
                    that.panels = that.panels.add(panel);
                }

                if (originalAriaControls) {
                    tab.data('ui-tabs-aria-controls', originalAriaControls);
                }
                tab.attr({
                    'aria-controls': selector.substring(1),
                    'aria-labelledby': anchorId
                });
                panel.attr('aria-labelledby', anchorId);

                if (that.options.excludedPanel.indexOf(anchorId + '_content') < 0) {
                    panel.addClass(that.options.tabPanelClass);
                }
            });

            this.panels
                .addClass('ui-tabs-panel ui-widget-content ui-corner-bottom')
                .attr('role', 'tabpanel');
        },

        /**
         * Move panels in destination element
         * @protected
         * @override
         */
        _movePanelsInDestination: function (panels) {
            if (this.options.destination && !panels.parents(this.options.destination).length) {
                this.element.trigger('beforePanelsMove', panels);

                panels.find('script:not([type]), script[type="text/javascript"]').remove();

                panels.appendTo(this.options.destination)
                    .each($.proxy(function (i, panel) {
                        $(panel).trigger('move.tabs', this.anchors.eq(i));
                    }, this));
            }
        },

        /**
         * Move panels in destination element on tabs switching
         * @protected
         * @override
         * @param {Object} event - event object
         * @param {Object} eventData
         */
        _toggle: function (event, eventData) {
            this._movePanelsInDestination(eventData.newPanel);
            this._superApply(arguments);
        }
    });

    // Extension for mage.tabs - Ajax functionality for tabs
    $.widget('mage.tabs', $.mage.tabs, {
        options: {
            /**
             * Add form key to ajax call
             * @param {Object} event - event object
             * @param {Object} ui
             */
            beforeLoad: function (event, ui) {
                ui.ajaxSettings.type = 'POST';
                ui.ajaxSettings.hasContent = true;
                ui.jqXHR.setRequestHeader('Content-Type', ui.ajaxSettings.contentType);
                ui.ajaxSettings.data = jQuery.param(
                    {
                        isAjax: true,
                        'form_key': typeof FORM_KEY !== 'undefined' ? FORM_KEY : null
                    },
                    ui.ajaxSettings.traditional
                );
            },

            /**
             * Replacing href attribute with loaded panel id
             * @param {Object} event - event object
             * @param {Object} ui
             */
            load: function (event, ui) {
                var panel = $(ui.panel);

                $(ui.tab).prop('href', '#' + panel.prop('id'));
                panel.trigger('contentUpdated');
            }
        }
    });

    // Extension for mage.tabs - Attach event handlers to tabs
    $.widget('mage.tabs', $.mage.tabs, {
        options: {
            tabIdArgument: 'tab',
            tabsBlockPrefix: null
        },

        /**
         * Attach event handlers to tabs, on creation
         * @protected
         * @override
         */
        _refresh: function () {
            this._super();
            $.each(this.tabs, $.proxy(function (i, tab) {
                $(this._getPanelForTab(tab))
                    .off('changed' + this.eventNamespace)
                    .off('highlight.validate' + this.eventNamespace)
                    .off('focusin' + this.eventNamespace)

                    .on('changed' + this.eventNamespace, {
                        index: i
                    }, $.proxy(this._onContentChange, this))
                    .on('highlight.validate' + this.eventNamespace, {
                        index: i
                    }, $.proxy(this._onInvalid, this))
                    .on('focusin' + this.eventNamespace, {
                        index: i
                    }, $.proxy(this._onFocus, this));
            }, this));

            ($(this.options.destination).is('form') ?
                $(this.options.destination) :
                $(this.options.destination).closest('form'))
                    .off('beforeSubmit' + this.eventNamespace)
                    .on('beforeSubmit' + this.eventNamespace, $.proxy(this._onBeforeSubmit, this));
        },

        /**
         * Mark tab as changed if some field inside tab panel is changed
         * @protected
         * @param {Object} e - event object
         */
        _onContentChange: function (e) {
            var cssChanged = '_changed';

            this.anchors.eq(e.data.index).addClass(cssChanged);
            this._updateNavTitleMessages(e, cssChanged);
        },

        /**
         * Clone messages (tooltips) from anchor to parent element
         * @protected
         * @param {Object} e - event object
         * @param {String} messageType - changed or error
         */
        _updateNavTitleMessages: function (e, messageType) {
            var curAnchor = this.anchors.eq(e.data.index),
                curItem = curAnchor.parents('[data-role="container"]').find('[data-role="title"]'),
                curItemMessages = curItem.find('[data-role="title-messages"]'),
                activeClass = '_active';

            if (curItemMessages.is(':empty')) {
                curAnchor
                    .find('[data-role="item-messages"]')
                    .clone()
                    .appendTo(curItemMessages);
            }

            curItemMessages.find('.' + messageType).addClass(activeClass);
        },

        /**
         * Mark tab as error if some field inside tab panel is not passed validation
         * @param {Object} e - event object
         * @protected
         */
        _onInvalid: function (e) {
            var cssError = '_error',
                fakeEvent = e;

            fakeEvent.currentTarget = $(this.anchors).eq(e.data.index);
            this._eventHandler(fakeEvent);
            this.anchors.eq(e.data.index).addClass(cssError).find('.' + cssError).show();
            this._updateNavTitleMessages(e, cssError);
        },

        /**
         * Show tab panel if focus event triggered of some field inside tab panel
         * @param {Object} e - event object
         * @protected
         */
        _onFocus: function (e) {
            this.option('_active', e.data.index);
        },

        /**
         * Add active tab id in data object when "beforeSubmit" event is triggered
         * @param {Object} e - event object
         * @param {Object} data - event data object
         * @protected
         */
        _onBeforeSubmit: function (e, data) { //eslint-disable-line no-unused-vars
            var activeAnchor = this.activeAnchor(),
                activeTabId = activeAnchor.prop('id'),
                options;

            if (this.options.tabsBlockPrefix) {
                if (activeAnchor.is('[id*="' + this.options.tabsBlockPrefix + '"]')) {
                    activeTabId = activeAnchor.prop('id').substr(this.options.tabsBlockPrefix.length);
                }
            }
            $(this.anchors).removeClass('error');
            options = {
                action: {
                    args: {}
                }
            };
            options.action.args[this.options.tabIdArgument] = activeTabId;
            data = data ? $.extend(data, options) : options;
        }
    });

    // Extension for mage.tabs - Shadow tabs functionality
    $.widget('mage.tabs', $.mage.tabs, {
        /**
         * Add shadow tabs functionality on creation
         * @protected
         * @override
         */
        _refresh: function () {
            var anchors, shadowTabs, tabs;

            this._super();
            anchors = this.anchors;
            shadowTabs = this.options.shadowTabs;
            tabs = this.tabs;

            if (shadowTabs) {
                anchors.each($.proxy(function (i, anchor) {
                    var anchorId = $(anchor).prop('id');

                    if (shadowTabs[anchorId]) {
                        $(anchor).parents('li').on('click', $.proxy(function () {
                            $.each(shadowTabs[anchorId], $.proxy(function (key, id) {
                                this.load($(tabs).index($('#' + id).parents('li')), {});
                            }, this));
                        }, this));
                    }
                }, this));
            }
        }
    });

    return $.mage.tabs;
});

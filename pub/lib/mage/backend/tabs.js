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
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true browser:true*/
/*global FORM_KEY:true*/
(function($) {
    'use strict';
    // mage.tabs base functionality
    $.widget('mage.tabs', $.ui.tabs, {
        options: {
            spinner: false,
            groups: null
        },

        /**
         * Tabs creation
         * @protected
         */
        _create: function() {
            var activeIndex = this._getTabIndex(this.options.active);
            this.options.active = activeIndex >= 0 ? activeIndex : 0;
            this._super();
        },

        /**
         * @override
         * @private
         * @return {Array} Array of DOM-elements
         */
        _getList: function() {
            if(this.options.groups) {
                return this.element.find(this.options.groups);
            } else {
                return this._super();
            }
        },

        /**
         * Get active anchor
         * @return {Element}
         */
        activeAnchor: function() {
            return this.anchors.eq(this.option("active"));
        },

        /**
         * Get tab index by tab id
         * @protected
         * @param {string} id - id of tab
         * @return {number}
         */
        _getTabIndex: function(id) {
            var anchors = this.anchors ?
                this.anchors :
                this._getList().find("> li > a[href]");
            return anchors.index($('#' + id));
        },

        /**
         * Switch between tabs
         * @protected
         * @param {Object} event - event object
         * @param {undefined|Object} eventData
         */
        _toggle: function(event, eventData) {
            var anchor = $(eventData.newTab).find('a');
            if ($(eventData.newTab).find('a').data().tabType === 'link') {
                location.href = anchor.prop('href');
            } else {
                this._superApply(arguments);
            }
        }
    });

    // Extension for mage.tabs - Move panels in destination element
    $.widget('mage.tabs', $.mage.tabs, {
        /**
         * Move panels in destination element on creation
         * @protected
         * @override
         */
        _create: function() {
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
        _getPanelForTab: function(tab) {
            var panel = this._superApply(arguments);
            if (!panel.length) {
                var id = $(tab).attr("aria-controls");
                panel = $(this.options.destination).find(this._sanitizeSelector( "#" + id ));
            }
            return panel;
        },

        /**
         * Move panels in destination element
         * @protected
         * @override
         * @param {Array} panels - array of panels DOM elements
         */
        _movePanelsInDestination: function(panels) {
            if (this.options.destination && !panels.parents(this.options.destination).length) {
                panels
                    .find('script').remove();
                panels.appendTo(this.options.destination)
                    .each($.proxy(function(i, panel) {
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
        _toggle: function(event, eventData) {
            this._movePanelsInDestination(eventData.newPanel);
            this._superApply(arguments);
        }
    });

    // Extension for mage.tabs - Ajax functionality for tabs
    $.widget('mage.tabs', $.mage.tabs, {
        options: {
            ajaxOptions: {
                data: {
                    isAjax: true,
                    form_key: typeof FORM_KEY !== 'undefined' ? FORM_KEY : null
                }
            },

            /**
             * Replacing href attribute with loaded panel id
             * @param {Object} event - event object
             * @param {Object}
             */
            load: function(event, ui) {
                $(ui.tab).prop('href', '#' + $(ui.panel).prop('id'));
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
        _create: function() {
            this._super();
            this._bind();
        },

        /**
         * Attach event handlers to tabs
         * @protected
         */
        _bind: function() {
            $.each(this.panels, $.proxy(function(i, panel) {
                $(panel)
                    .on('changed', {index: i}, $.proxy(this._onContentChange, this))
                    .on('highlight.validate', {index: i}, $.proxy(this._onInvalid, this))
                    .on('focusin', {index: i}, $.proxy(this._onFocus, this));
            }, this));

            ($(this.options.destination).is('form') ?
                $(this.options.destination) :
                $(this.options.destination).closest('form'))
                    .on('beforeSubmit', $.proxy(this._onBeforeSubmit, this));
        },

        /**
         * Mark tab as changed if some field inside tab panel is changed
         * @protected
         * @param {Object} e - event object
         */
        _onContentChange: function(e) {
            this.anchors.eq(e.data.index).addClass('changed');
        },

        /**
         * Mark tab as error if some field inside tab panel is not passed validation
         * @param {Object} e - event object
         * @protected
         */
        _onInvalid: function(e) {
            this.anchors.eq(e.data.index).addClass('error').find('.error').show();
        },

        /**
         * Show tab panel if focus event triggered of some field inside tab panel
         * @param {Object} e - event object
         * @protected
         */
        _onFocus: function(e) {
            this.option("active", e.data.index);
        },

        /**
         * Add active tab id in data object when "beforeSubmit" event is triggered
         * @param {Object} e - event object
         * @param {Object} data - event data object
         * @protected
         */
        _onBeforeSubmit: function(e, data) {
            var activeAnchor = this.activeAnchor(),
                activeTabId = activeAnchor.prop('id');
            if (this.options.tabsBlockPrefix) {
                if (activeAnchor.is('[id*="' + this.options.tabsBlockPrefix + '"]')) {
                    activeTabId = activeAnchor.prop('id').substr(this.options.tabsBlockPrefix.length);
                }
            }
            $(this.anchors).removeClass('error');
            var options = {
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
        _bind: function() {
            this._super();
            this._bindShadowTabs();
        },

        /**
         * Process shadow tabs
         * @protected
         */
        _bindShadowTabs: function() {
            var anchors = this.anchors,
                shadowTabs = this.options.shadowTabs,
                tabs = this.tabs;

            if (shadowTabs) {
                anchors.each($.proxy(function(i, anchor) {
                    var anchorId = $(anchor).prop('id');
                    if (shadowTabs[anchorId]) {
                        $(anchor).parents('li').on('click', $.proxy(function() {
                            $.each(shadowTabs[anchorId], $.proxy(function(i, id) {
                                this.load($(tabs).index($('#' + id).parents('li')), {});
                            }, this));
                        }, this));
                    }
                }, this));
            }
        }
    });
})(jQuery);

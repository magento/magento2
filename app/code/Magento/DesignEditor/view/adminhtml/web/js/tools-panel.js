/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    'use strict';
    
    /**
     * VDE tool panel
     */
    $.widget('vde.toolsPanel', {
        options: {
            openedPanelClass: 'opened',
            activeTabClass: 'active',
            panelDefaultHeight: 300,
            panelMinHeight: 100,
            panelMaxHeight: 700,
            showHidePanelAnimationSpeed: 300,
            resizableArea: '.vde-tools-content',
            resizableAreaInner: '.vde-tab-content.active .vde-tab-content-inner',
            panelHeader: '.vde-tab-content-header',
            panelTab: 'a[data-toggle="tab"]',
            resizeHandlerControl: '.ui-resizable-handle',
            resizeHandlerControlContainer: '.vde-tools-handler-container',
            scrollExistClass: 'hasScroll',
            mainTabs: '.vde-tools-footer .vde-tab-controls > .item',
            btnClose: '.vde-tools-header .action-close',
            btnCloseMsg: '.vde-message .action-close'
        },
        /**
         * Widget initialization
         * @protected
         */
        _create: function() {
            this.panel = this.element;

            this.resizableArea = $(this.options.resizableArea);
            this.resizableAreaInner = $(this.options.resizableAreaInner);
            this.panelTab = $(this.options.panelTab);
            this.resizeHandlerControlContainer = $(this.options.resizeHandlerControlContainer);
            this.panelHeaderHeight = $(this.options.panelHeader).height();
            this.btnClose = $(this.options.btnClose, this.panel);
            this.mainTabs = $(this.options.mainTabs);
            this.btnCloseMsg = $(this.options.btnCloseMsg, this.panel);

            this._events();
        },
        /**
         * Init tool panel and prepare sizes
         * @protected
         */
        _init: function() {
            $(this.options.resizeHandlerControl).prependTo(this.resizeHandlerControlContainer);
            this._recalcDataHeight(this._getResizableAreaHeight());
        },
        /**
         * Init tool panel events
         * @protected
         */
        _events: function() {
            var self = this;
            this.resizableArea.resizable({
                handles: 'n',
                minHeight: self.options.panelMinHeight,
                maxHeight: self.options.panelMaxHeight,
                iframeFix: true,
                resize: function(event, ui) {
                    self._recalcDataHeight(ui.size.height);
                },
                stop: function(event, ui) {
                    if (ui.size.height <= self.options.panelMinHeight) {
                        self._hide();
                    }
                }
            }).bind('resize.vdeToolsResize', function () {
                self._recalcDataHeight(self._getResizableAreaHeight());
                $(this).css('top', 'auto');
            });

            this.panelTab.on('click.tab.data-api', $.proxy(this._onPanelTabClick, self));

            $('body')
                .on('addMessage clearMessages', $.proxy(function() {
                    this.resizableArea.trigger('resize.vdeToolsResize');
                }, this));

            this.panelTab.on('shown', function () {
                if (self.panel.hasClass(self.options.openedPanelClass)) {
                    self._recalcDataHeight(self.options.panelDefaultHeight);
                }
                self.resizableArea.trigger('resize.vdeToolsResize');
            });

            this.mainTabs.on('click', function() {
                if ($(this).hasClass('disabled')) {
                    return;
                }
                var isPanelOpen = self.panel.hasClass(self.options.openedPanelClass);
                var isReopen = isPanelOpen && !$(this).hasClass(self.options.activeTabClass);

                if (isPanelOpen && !isReopen) {
                    self._hide();
                } else {
                    self._show(isReopen);
                }
            });

            this.btnClose.on('click.hideVDEToolsPanel', $.proxy(this._hide, this));

            this.btnCloseMsg.on('click.hideVDEMessage', $.proxy(function(e) {
                $(e.target).parents('.vde-message')[0].remove();
            }, this));
        },
        /**
         * Panel tab click event handler.
         * Fire an event to determine if inline translation text is being edited.
         * @protected
         */
        _onPanelTabClick: function(event) {
            var data = {
                next_action: this.options.panelTab,
                alert_message: "To switch modes, please save or revert your current text edits."
            };
            $('[data-frame="editor"]').trigger('modeChange', data);

            if (data.is_being_edited) {
                event.stopPropagation();
            }
        },
        /**
         * Toggle hasScroll class if scroll is necessary
         * @param elem
         * @protected
         */
        _toggleClassIfScrollBarExist: function(elem) {
            elem.toggleClass(
                this.options.scrollExistClass,
                elem.height() < $('.vde-tab-data', elem).height() + $('.vde-tools-header').height()
            );
        },
        /**
         * Get resizable element
         * @returns {HTMLElement}
         * @protected
         */
        _getActiveResizableAreaInner: function() {
            return $(this.options.resizableAreaInner);
        },
        /**
         * Get resizable element height
         * @returns {int}
         * @protected
         */
        _getResizableAreaHeight: function() {
            return this.resizableArea.height();
        },
        /**
         * Recalculate height for resalable element
         * @param height
         * @protected
         */
        _recalcDataHeight: function(height) {
            var elem = this._getActiveResizableAreaInner();

            elem.height(height - this.panelHeaderHeight);
            this._toggleClassIfScrollBarExist(elem);

            this._getActiveResizableAreaInner().scrollTop(0);
        },
        /**
         * Open/Reopen panel
         * @param isReopen
         * @protected
         */
        _show: function(isReopen) {
            this.panel.addClass(this.options.openedPanelClass);
            if (!isReopen) {
                this.resizableArea.animate({
                    height: this.options.panelDefaultHeight - this.panelHeaderHeight
                }, this.options.showHidePanelAnimationSpeed, $.proxy(function() {
                    this.resizableArea.trigger('resize.vdeToolsResize');
                    $(window).trigger('resize');
                }, this));
            }
        },
        /**
         * Hide panel
         * @protected
         */
        _hide: function() {
            this.panel.removeClass(this.options.openedPanelClass);

            this.resizableArea.animate({
                height: 0
            }, this.options.showHidePanelAnimationSpeed, $.proxy(function() {
                this.mainTabs.removeClass(this.options.activeTabClass);
                $(window).trigger('resize');
            }, this));
        }
    });

});
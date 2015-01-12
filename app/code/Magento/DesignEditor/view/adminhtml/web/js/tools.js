/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function(jQuery){
    'use strict';

    $.widget("vde.translateInlineToggle", {
        showMenu: false,

        _create: function () {
            var that = this;

            $('body').on('click', function(e) {
                var widgetInstancesSelector = ':' + that.namespace + '-' + that.widgetName;
                // If the target is not a child of the widget, then hide the menu, since clicking outside the menu.
                $(widgetInstancesSelector).not($(e.target).parents(widgetInstancesSelector)).translateInlineToggle('hide');
            });

            this.element.find('[data-translate-selected]').on('click', function(){
                that._toggle($(this).data('translateSelected'));
            });

            this.element.find('[vde-translate]')
                .on('mouseover', $.proxy(this._onMouseOver, this))
                .on('mouseout', $.proxy(this._onMouseOut, this))
                .on('mousedown', $.proxy(this._onMouseDown, this))
                .on('mouseup', $.proxy(this._onMouseUp, this))
                .on('disableInlineTranslation', $.proxy(this._disableInlineTranslation, this));
        },

        hide: function() {
            // If the menu is not already hidden, hide it.
            if (!this.element.find('[data-translate-menu]').hasClass('hidden'))
                this.element.find('[data-translate-menu]').toggleClass('hidden');
        },

        /**
         * Disable inline translation.
         *
         * @param event
         * @param data
         * @private
         */
        _disableInlineTranslation: function (event, data) {
            if (data.mode != '') {
                var originalRefreshVdeCanvas = this.options.refreshVdeCanvas;
                var originalFrameUrl = this.options.frameUrl;
                this.options.refreshVdeCanvas = false;
                this.options.frameUrl = data.frameUrl;

                this._toggle(data.mode);

                this.options.refreshVdeCanvas = originalRefreshVdeCanvas;
                this.options.frameUrl = originalFrameUrl;
            }
        },

        /**
         * If the menu is not being shown, show the tooltip.
         *
         * @private
         */
        _onMouseOver: function () {
            if (this.element.find('[data-translate-menu]').hasClass('hidden'))
                this.element.find('[data-tip="translate"]').removeClass('hidden');
        },

        /**
         * Hide the tooltip.
         *
         * @private
         */
        _onMouseOut: function () {
            this.element.find('[data-tip="translate"]').addClass('hidden');
        },

        /**
         * If the mouse button has been held down for more than 1 second, the menu will be displayed.
         *
         * @private
         */
        _onMouseDown: function () {
            this._onMouseOut();
            this.showMenu = false;

            var that = this;
            clearTimeout(this.downTimer);
            this.downTimer = setTimeout(function() {
                that.element.find('[data-translate-menu]').toggleClass('hidden');
                that.showMenu = true;
            }, 1000);
        },

        /**
         * If the menu is not displaying (didn't hold down button long enough), toggle, else hide the tooltip.
         *
         * @private
         */
        _onMouseUp: function () {
            if (this.element.find('[data-translate-menu]').hasClass('hidden')) {
                this._toggle(this.element.find('[data-translate-edit]').data('translate-edit'));
            } else {
                // If the button is clicked while the menu is displaying, hide the menu.
                if (!this.showMenu)
                    this.element.find('[data-translate-menu]').toggleClass('hidden');
                this.element.find('[data-tip="translate"]').addClass('hidden');
            }

            clearTimeout(this.downTimer);
        },

        /**
         * Toggle editing.
         *
         * @param mode
         * @private
         */
        _toggle: function (mode) {
            // Hide menu.
            if (!this.element.find('[data-translate-menu]').hasClass('hidden')) {
                this.element.find('[data-translate-menu]').toggleClass('hidden');
            }

            // Change menu to reflect what was selected, so will display correctly when displayed again.
            var disableInlineTranslation = this._updateMenu(mode);

            // Refresh iframe with the new url.
            this._refresh(mode, disableInlineTranslation);
        },

        /**
         * Refresh the iframe.
         *
         * @param mode
         * @param disableInlineTranslation
         * @private
         */
         _refresh: function (mode, disableInlineTranslation) {
            // If this is the first time selecting a mode, refresh the iframe to wrap all the applicable content.
            // Or, if disabling inline translation, refresh without the mode on the url.
            var url = this.options.frameUrl;

            if (this.options.refreshVdeCanvas || disableInlineTranslation) {
                if (this.options.refreshVdeCanvas)
                    url = url + "?" + $.param({translation_mode: mode});

                $('[data-frame="editor"]').prop('src', url);

                /**
                 * Since the url is being modified to support inline translation, the window is not reloaded since it
                 * is using the cached url to display.
                 */
            } else {
                this.options.dialogWidgetElement.translateInlineDialogVde('toggleStyle', mode);
            }
         },

        /**
         * Update the menu and toolbar button.
         *
         * @param mode
         * @private
         */
        _updateMenu: function (mode) {
            function _toggleSelected(translateOption, backgroundRemove, backgroundAdd, imageRemove, imageAdd) {
                translateOption.removeClass(backgroundRemove).addClass(backgroundAdd);
                translateOption.find('[data-translate-img]').removeClass(imageRemove).addClass(imageAdd);
            }

            var disableInlineTranslation = false;

            var TEXT_MENU_BACKGROUND_ON = 'text-menu-background-on';
            var TEXT_MENU_BACKGROUND_OFF = 'text-menu-background-off';

            var textMenuClass = 'text-menu-' + mode;
            var textEditClass = 'text-edit-' + mode;

            var toolbarButton = this.element.find('[data-translate-edit]');

            toolbarButton.data('translate-edit', mode);

            var that = this;
            this.element.find('[data-translate-selected]').each(function() {
                if ($(this).data('translate-selected') === mode) {
                    // Check to see if turning off (selecting the already highlighted option).
                    if ($(this).hasClass(TEXT_MENU_BACKGROUND_ON)) {
                        // Update toolbar button.
                        toolbarButton.removeClass(textEditClass + '-on');
                        toolbarButton.addClass(textEditClass + '-off');

                        // Disable option.
                        _toggleSelected($(this), TEXT_MENU_BACKGROUND_ON, TEXT_MENU_BACKGROUND_OFF, textMenuClass + '-on', textMenuClass + '-off');

                        // Refresh iframe without the mode on the url.
                        disableInlineTranslation = true;
                    }
                    else {
                        // Update toolbar button.
                        toolbarButton.removeClass(textEditClass + '-off');
                        toolbarButton.addClass(textEditClass + '-on');

                        // Enable selected option
                        _toggleSelected($(this), TEXT_MENU_BACKGROUND_OFF, TEXT_MENU_BACKGROUND_ON, textMenuClass + '-off', textMenuClass + '-on');
                    }

                    // Update tooltip text.
                    that.element.find('[data-tip-text]').text("Toggle " + $(this).find('[data-translate-label]').html());
                }
                else {
                    var translateOptionMode = $(this).data('translate-selected');
                    var translateOptionModeClass = 'text-menu-' + translateOptionMode;
                    var translateEditModeClass = 'text-edit-' + translateOptionMode;

                    // Update toolbar button.
                    toolbarButton.removeClass(translateEditModeClass + '-on');
                    toolbarButton.removeClass(translateEditModeClass + '-off');

                    // Disable option.
                    _toggleSelected($(this), TEXT_MENU_BACKGROUND_ON, TEXT_MENU_BACKGROUND_OFF, translateOptionModeClass + '-on', translateOptionModeClass + '-off');
                }
            });

            return disableInlineTranslation;
        }
    });

});
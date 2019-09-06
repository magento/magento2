/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.popupWindow', {
        options: {
            centerBrowser: 0, // center window over browser window? {1 (YES) or 0 (NO)}. overrides top and left
            centerScreen: 0, // center window over entire screen? {1 (YES) or 0 (NO)}. overrides top and left
            height: 500, // sets the height in pixels of the window.
            left: 0, // left position when the window appears.
            location: 0, // determines whether the address bar is displayed {1 (YES) or 0 (NO)}.
            menubar: 0, // determines whether the menu bar is displayed {1 (YES) or 0 (NO)}.
            resizable: 0, // whether the window can be resized {1 (YES) or 0 (NO)}.
            scrollbars: 0, // determines whether scrollbars appear on the window {1 (YES) or 0 (NO)}.
            status: 0, // whether a status line appears at the bottom of the window {1 (YES) or 0 (NO)}.
            width: 500, // sets the width in pixels of the window.
            windowName: null, // name of window set from the name attribute of the element that invokes the click
            windowURL: null, // url used for the popup
            top: 0, // top position when the window appears.
            toolbar: 0 // determines whether a toolbar is displayed {1 (YES) or 0 (NO)}.
        },

        /**
         * @private
         */
        _create: function () {
            this.element.on('click', $.proxy(this._openPopupWindow, this));
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _openPopupWindow: function (event) {
            var element = $(event.target),
                settings = this.options,
                windowFeatures =
                    'height=' + settings.height +
                        ',width=' + settings.width +
                        ',toolbar=' + settings.toolbar +
                        ',scrollbars=' + settings.scrollbars +
                        ',status=' + settings.status +
                        ',resizable=' + settings.resizable +
                        ',location=' + settings.location +
                        ',menuBar=' + settings.menubar,
                centeredX,
                centeredY;

            settings.windowName = settings.windowName || element.attr('name');
            settings.windowURL = settings.windowURL || element.attr('href');

            if (settings.centerBrowser) {
                centeredY = window.screenY + ((window.outerHeight / 2 - settings.height / 2));
                centeredX = window.screenX + ((window.outerWidth / 2 - settings.width / 2));
                windowFeatures += ',left=' + centeredX + ',top=' + centeredY;
            } else if (settings.centerScreen) {
                centeredY = (screen.height - settings.height) / 2;
                centeredX = (screen.width - settings.width) / 2;
                windowFeatures += ',left=' + centeredX + ',top=' + centeredY;
            } else {
                windowFeatures += ',left=' + settings.left + ',top=' + settings.top;
            }

            window.open(settings.windowURL, settings.windowName, windowFeatures).focus();
            event.preventDefault();
        }
    });

    return $.mage.popupWindow;
});

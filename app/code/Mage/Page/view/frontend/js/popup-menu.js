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
 * @category    popup-menu
 * @package     js
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($) {
    $.widget('mage.popUpMenu', {
        options: {
            eventClass: 'faded', // Class applied to popUpMenu with mouseleave/mouseenter events.
            fadeDuration: 'slow', // Duration for the fade effect when popUpMenu is shown/hidden.
            hideOnClick: true, // Hides the popUpMenu when click anywhere in the document body.
            menu: '', // Selector for the popUpMenu (e.g. <ul>).
            onMouseEnter: null, // Function called when mouseenter event is triggered on popUpMenu.
            onMouseLeave: null, // Function called when mouseleave event is triggered on popUpMenu.
            openedClass: 'list-opened', // Class applied to switcher when popUpMenu is shown/hidden.
            switcher: 'span.switcher', // Selector for the popUpMenu switcher.
            timeoutDuration: 2000 // Duration before popUpMenu is hidden after mouseleave event.
        },

        /**
         * Add click event to the switcher. Add blur, mouseenter/mouseleave events to the
         * containing element.
         * @private
         */
        _create: function() {
            this.switcher = this.element.find(this.options.switcher)
                .on('click', $.proxy(this._toggleMenu, this));
            var eventMap = {
                mouseenter: $.proxy(this.options.onMouseEnter, this),
                mouseleave: $.proxy(this.options.onMouseLeave, this)
            };
            if (this.options.hideOnClick) {
                eventMap.blur = $.proxy(this._hide, this);
            }
            this.element.on(eventMap);
            $(this.options.menu).find('a').on('click', $.proxy(this._hide, this));
        },

        /**
         * Custom method for defining options during instantiation. User-provided options
         * override the options returned by this method which override the default options.
         * @private
         * @return {Object} Object containing options for mouseenter/mouseleave events.
         */
        _getCreateOptions: function() {
            return {onMouseEnter: this._onMouseEnter, onMouseLeave: this._onMouseLeave};
        },

        /**
         * Hide the popup menu using a fade effect.
         * @private
         */
        _hide: function(){
            $(this.options.menu).fadeOut(this.options.fadeDuration, $.proxy(this._stopTimer, this));
            this.switcher.removeClass(this.options.openedClass);
        },

        /**
         * Show the popup menu using a fade effect and put focus on the containing element for
         * the blur event.
         * @private
         */
        _show: function() {
            $(this.options.menu)
                .removeClass(this.options.eventClass).fadeIn(this.options.fadeDuration);
            this.switcher.addClass(this.options.openedClass);
            if (this.options.hideOnClick) {
                this.element.focus();
            }
        },

        /**
         * Stop (clear) the timeout.
         * @private
         */
        _stopTimer: function() {
            clearTimeout(this.timer);
        },

        /**
         * Determines whether the popup menu is open (show) or closed (hide).
         * @private
         * @return boolean Returns true if open, false otherwise.
         */
        _isOpened: function() {
            return this.switcher.hasClass(this.options.openedClass);
        },

        /**
         * Mouseleave event on the popup menu. Add faded class and set appropriate timeout.
         * @private
         */
        _onMouseLeave: function() {
            if (this._isOpened()) {
                $(this.options.menu).addClass(this.options.eventClass);
                this._stopTimer();
                this.timer = setTimeout($.proxy(this._hide, this), this.options.timeoutDuration);
            }
        },

        /**
         * Mouseenter event on the popup menu. Reset the timer and remove the faded class.
         * @private
         */
        _onMouseEnter: function() {
            if (this._isOpened()) {
                this._stopTimer();
                $(this.options.menu).removeClass(this.options.eventClass);
            }
        },

        /**
         * Toggle the state of the popup menu. Open it (show) or close it (hide).
         * @private
         * @return {*}
         */
        _toggleMenu: function() {
            return this[this._isOpened() ? '_hide' : '_show']();
        }
    });
})(jQuery);

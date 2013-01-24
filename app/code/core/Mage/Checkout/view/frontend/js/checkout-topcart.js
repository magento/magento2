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
 * @category    mage product view
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $.widget('mage.topCart', {
        options: {
            intervalDuration: 4000
        },

        _create: function(){
            this.element.find(this.options.closeSelector)
                .on('click', $.proxy(this.hide, this));
            this.element.parent()
                .on('mouseleave', $.proxy(this._onMouseleave, this))
                .on('mouseenter', $.proxy(this._stopTimer, this));
            this.element.prev().on('click', $.proxy(function () {
                this.element.slideToggle('slow');
            }, this));
        },

        /**
         * Hide (slide up) the checkout top-cart.
         */
        hide: function(){
            this.element.slideUp('slow', $.proxy(this._stopTimer, this));
        },

        /**
         * Clear (stop) the timer that controls the show/hide of the checkout top-cart.
         * @private
         */
        _stopTimer: function() {
            clearTimeout(this.timer);
        },

        /**
         * Executes when the mouse leaves the top-cart area. Initiates hiding of the top-cart
         * after a set interval duration.
         * @private
         */
        _onMouseleave: function() {
            this._stopTimer();
            this.timer = setTimeout($.proxy(this.hide, this), this.options.intervalDuration);
        }
    });
})(jQuery);
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($, window) {
    $.widget('mage.sticky', {
        options: {
            container: ''
        },

        /**
         * Bind handlers to scroll event
         * @private
         */
        _create: function() {
            $(window).on('scroll', $.proxy(this._setTop, this));
        },

        /**
         * float Block on windowScroll
         * @private
         */
        _setTop: function() {
            if ((this.element).is(':visible')) {
                var startOffset = this.element.parent().offset().top + parseInt(this.element.css("margin-top"), 10),
                    currentOffset = $(document).scrollTop(),
                    parentHeight = $(this.options.container).height() - parseInt(this.element.css("margin-top"), 10),
                    discrepancyOffset = currentOffset - startOffset;

                if (discrepancyOffset >= 0) {
                    if (discrepancyOffset + this.element.innerHeight() < parentHeight) {
                        this.element.css('top', discrepancyOffset);
                    }
                } else {
                    this.element.css('top', 0);
                }
            }
        }
    });
})(jQuery, window);

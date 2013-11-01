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
 * @category    frontend home menu
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true */
(function ($) {
    $.widget('mage.menu', {
        options: {
            showDelay: 100,
            hideDelay: 100
        },

        _create: function() {
            this.element.hover($.proxy(function () {
                $(this.element).addClass('over');
                this._show(this.element.children('ul'));
            }, this), $.proxy(function () {
                $(this.element).removeClass('over');
                this._hide(this.element.children('ul'));
            }, this));
        },

        /**
         * Show sub menu by adding shown-sub class
         * @private
         * @param subElement
         */
        _show: function(subElement) {
            if (subElement.data('hideTimeId')) {
                clearTimeout(subElement.data('hideTimeId'));
            }
            subElement.data('showTimeId', setTimeout(function () {
                subElement.addClass('shown-sub');
            }), this.options.showDelay);
        },

        /**
         * Hide sub menu by removing shown-sub class
         * @private
         * @param subElement
         */
        _hide: function(subElement) {
            if (subElement.data('showTimeId')) {
                clearTimeout(subElement.data('showTimeId'));
            }
            subElement.data('hideTimeId', setTimeout(function () {
                subElement.removeClass('shown-sub');
            }), this.options.hideDelay);
        }
    });
})(jQuery);

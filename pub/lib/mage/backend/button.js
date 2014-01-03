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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    'use strict';
    $.widget('ui.button', $.ui.button, {
        options: {
            eventData: {}
        },

        /**
         * Button creation
         * @protected
         */
        _create: function() {
            if (this.options.event) {
                this.options.target = this.options.target || this.element;
                this._bind();
            }
            this._super();
        },

        /**
         * Bind handler on button click
         * @protected
         */
        _bind: function() {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(function() {
                    $(this.target).trigger(this.event, [this.eventData]);
                }, this.options));
        }
    });
})(jQuery);

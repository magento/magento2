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
define(["jquery","jquery/ui"], function($){
    "use strict";
    $.widget('mage.checkoutBalance', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function() {
            this.eventData = {
                price: this.options.balance,
                totalPrice: 0
            };
            this.element.on('change', $.proxy(function(e) {
                if ($(e.target).is(':checked')) {
                    this.eventData.price = -1 * this.options.balance;
                } else {
                    if (this.options.amountSubstracted) {
                        this.eventData.price = this.options.usedAmount;
                        this.options.amountSubstracted = false;
                    } else {
                        this.eventData.price = this.options.balance;
                    }
                }
                this.element.trigger('updateCheckoutPrice', this.eventData);
            }, this));
        }
    });
});
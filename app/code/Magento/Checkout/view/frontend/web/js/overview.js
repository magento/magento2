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
 * @category    checkout multi-shipping review order overview
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";
    
    $.widget('mage.orderOverview', {
        options: {
            opacity: 0.5, // CSS opacity for the 'Place Order' button when it's clicked and then disabled.
            pleaseWaitLoader: 'span.please-wait', // 'Submitting order information...' Ajax loader.
            placeOrderSubmit: 'button[type="submit"]', // The 'Place Order' button.
            agreements: '#checkout-agreements' // Container for all of the checkout agreements and terms/conditions
        },

        /**
         * Bind a submit handler to the form.
         * @private
         */
        _create: function() {
            this.element.on('submit', $.proxy(this._showLoader, this));
        },

        /**
         * Verify that all agreements and terms/conditions are checked. Show the Ajax loader. Disable
         * the submit button (i.e. Place Order).
         * @return {Boolean}
         * @private
         */
        _showLoader: function() {
            if ($(this.options.agreements).find('input[type="checkbox"]:not(:checked)').length > 0) {
                alert($.mage.__('Please agree to all Terms and Conditions before placing the orders.'));
                return false;
            }
            this.element.find(this.options.pleaseWaitLoader).show().end()
                .find(this.options.placeOrderSubmit).prop('disabled', true).css('opacity', this.options.opacity);
            return true;
        }
    });

});
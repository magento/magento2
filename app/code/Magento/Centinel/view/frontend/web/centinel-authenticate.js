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
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.centinelAuthenticate', {
        options : {
            frameUrl: '',
            iframeSelector: '[data-container="iframe"]',
            bodySelector: '[data-container="body"]'
        },

        _create : function() {
            // listen for the custom event for changing state
            this.element.closest(this.options.bodySelector).on("paymentAuthentication", $.proxy(this._paymentmentAthenticationTrigger, this));
        },

        _init: function() {
            this._isAuthenticationStarted = false;

            // show the frame with the appropriate URL
            this.element.find(this.options.iframeSelector).prop('src', this.options.frameUrl);
            this.element.show();
        },

        /**
         * This method is used to cancel the call to Centinel from a display perspective as it shows the related blocks and hides the frame.
         * @public
         */
        cancel : function() {
            this.element.hide();
            this.element.find(this.options.iframeSelector).prop('src', '');
            this._isAuthenticationStarted = false;
        },

        /**
         * This method is used to complete the interaction from Centinel and resets the display so the order can be placed.
         * @public
         */
        success : function() {
            this.element.hide();
            this._isAuthenticationStarted = false;
        },

        /**
         * This method processes the paymentAuthentication actions.
         */
        _paymentmentAthenticationTrigger : function(event, data) {
            if (data.state === 'start') {
                this._start();
            } else if (data.state === 'success') {
                this.success();
            } else if (data.state === 'cancel') {
                this.cancel();
            }
        },

        /**
         * This method is used to initiate the call to Centinel from a display perspective as it hides the related blocks and shows the frame. It also sets the URL in the frame, which initiates the interaction with Centinel.
         * @private
         */
        _start : function() {
            this._isAuthenticationStarted = true;
        }
    });

});
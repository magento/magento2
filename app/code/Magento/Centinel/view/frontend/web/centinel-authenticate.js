/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    
    return $.mage.centinelAuthenticate;
});
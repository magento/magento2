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
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";
        
    $.widget('mage.authorizenetAuthenticate', {
        options : {
            cancelButtonSelector: 'button[name="cancel"]',
            partialAuthorizationConfirmationMessage: '',
            cancelConfirmationMessage: '',
            cancelUrl: '',
            cancelPleaseWaitSelector: '#cancel-please-wait',
            checkoutPaymentMethodLoad: '#checkout-payment-method-load'
        },

        _create : function() {
            // listen for the custom event for changing state
            this.element.find(this.options.cancelButtonSelector).on("click", $.proxy(this._cancelPaymentAuthorizations, this));

            // go through the dialog if there is a message
            if (this.options.partialAuthorizationConfirmationMessage.length > 0) {
                this._confirmMessage(this.options.partialAuthorizationConfirmationMessage);
            }
        },

        _confirmMessage: function(msg) {
            if (!window.confirm(msg)) {
                this._cancelPaymentAuthorizations(true, true);
            }
        },

        _cancelPaymentAuthorizations: function(event, hideConfirm) {
            if (!hideConfirm && !window.confirm(this.options.cancelConfirmationMessage)) {
                window.alert($.mage.__("No confirmation"));
                return;
            }
            // this is a global selector due to the fact it is a sibling of the widget's html
            $(this.options.cancelPleaseWaitSelector).show();
            $.ajax({
                url: this.options.cancelUrl,
                type: 'get',
                dataType: 'json',
                context: this,
                success : function(response) {
                    $(this.options.cancelPleaseWaitSelector).hide();
                    if (response.success) {
                        this.element.find(this.options.cancelButtonSelector).remove();
                        // this is a global selector due to the fact it is not even close to the widget's html
                        $(this.options.checkoutPaymentMethodLoad).html(response.update_html).trigger('gotoSection', 'payment').trigger('contentUpdate');
                    } else {
                        var msg = response.error_message;
                        if (msg) {
                            window.alert($.mage.__(msg));
                        }
                    }
                }
            });
        }
    });

});
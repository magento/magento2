/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

    return $.mage.authorizenetAuthenticate;
});
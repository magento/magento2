/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "braintree",
    "jquery/ui"
], function($, braintree){
    "use strict";

    $.widget('mage.braintreePayPalShortcut', {
        options: {
            clientToken : "",
            currency : 'USD',
            amount : 0,
            locale: 'en_US',
            merchantName: null,
            container: '',
            submitFormId: null,
            enableBillingAddress: false,
            paymentMethodNonceId: null,
            paymentDetailsId: null
        },

        _create: function() {
            var clientToken = this.options.clientToken;
            var paymentDetailsId = this.options.paymentDetailsId;
            var submitFormId = this.options.submitFormId;
            var self = this;
            $('#'.concat(this.container)).empty();
            braintree.setup(clientToken, "paypal", {
                container: this.options.container,
                singleUse: true,
                amount: this.options.amount,
                currency: this.options.currency,
                enableShippingAddress: true,
                enableBillingAddress: this.options.enableBillingAddress,
                locale: this.options.locale,
                displayName: this.options.merchantName,
                onPaymentMethodReceived: function (obj) {
                    var nonce = obj.nonce;
                    var details = JSON.stringify(obj.details);
                    $('#'.concat(self.options.paymentMethodNonceId)).val(nonce);
                    $('#'.concat(paymentDetailsId)).val(details);
                    var hiddenForm = $('#'.concat(submitFormId))[0];
                    hiddenForm.submit();
                }
            });
        }
    });

    return $.mage.braintreePayPalShortcut;
});
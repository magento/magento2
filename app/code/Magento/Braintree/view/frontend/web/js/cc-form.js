/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "braintree",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function($, braintree, $t, alert) {
    "use strict";

    $.widget('mage.braintreeForm', {
        options: {
            loggedIn : false,
            formSelector: '#multishipping-billing-form',
            paymentMethodsSelector: '#payment-methods [name="payment[method]"]:checked',
            clientToken : "",
            useVault : false,
            autoDetection : false
        },

        _create: function() {
            $('#braintree_cc_number').bind('change', function(){
                $('#braintree_cc_last4').val($("#braintree_cc_number").val().slice(-4));
            });

            var self = this;
            if (this.options.useVault) {
                var selectBox = $('#braintree_cc_token');
                var initToken = selectBox.val();

                if (initToken)
                {
                    $('.hide_if_token_selected').hide();
                    $('#braintree_nonce').val("");
                }

                $('#braintree_cc_token').bind('change', function (e) {
                    var selectBox = $(this);
                    var token = selectBox.val();
                    if (token)
                    {
                        $('.hide_if_token_selected').hide();
                        $('#braintree_nonce').val("");
                    } else {
                        $('.hide_if_token_selected').show();

                        if (self.options.autoDetection) {
                            $('#cc_type_manual_row').hide();
                            $('#cc_type_auto_row').show();
                        } else {
                            $('#cc_type_manual_row').show();
                            $('#cc_type_auto_row').hide();
                        }
                    }
                });
            }

            //TODO: handle auto detection

            $(this.options.formSelector).bind('submit', function(event, form) {
                if ($(self.options.formSelector).find('input.mage-error').length > 0
                    || $(self.options.formSelector).find('select.mage-error').length > 0) {
                    return;
                }
                var selectedPaymentMethod = $(self.options.paymentMethodsSelector).val();
                if (selectedPaymentMethod != 'braintree') {
                    return;
                }
                var ccNumber = $("#braintree_cc_number").val();
                var ccExprYr = $("#braintree_expiration_yr").val();
                var ccExprMo = $("#braintree_expiration").val();
                var cvv = $('#braintree_cc_cid').val();

                if (ccNumber) {
                    var braintreeClient = new braintree.api.Client({clientToken: self.options.clientToken});
                    event.preventDefault();
                    braintreeClient.tokenizeCard(
                        {
                            number: ccNumber,
                            expirationMonth: ccExprMo,
                            expirationYear: ccExprYr,
                            cvv : cvv
                        },
                        function (err, nonce) {
                            if (!err) {
                                $('#braintree_nonce').val(nonce);
                                $("#braintree_cc_number").prop('disabled', true);;
                                $('#braintree_cc_cid').prop('disabled', true);;
                                var form = $(self.options.formSelector)[0];
                                form.submit();
                            } else {
                                alert({
                                    content: $t("An error occured with payment processing.")
                                });
                            }
                        }
                    );
                }
            });
        }
    });

    return $.mage.braintreeForm;
});
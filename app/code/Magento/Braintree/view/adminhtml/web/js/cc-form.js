/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "braintree",
    "jquery/ui"
], function($, braintree){
    "use strict";

    $.widget('mage.braintreeCcForm', {
        options: {
            submitSelectors: ['.save', '#submit_order_top_button'],
            clientToken : "",
            useVault : false,
            useCvv : false,
            code : "braintree",
            isFraudDetectionEnabled : false,
            braintreeDataJs : null
        },
        ccNumber: "",
        ccExprYr: "",
        ccExprMo: "",
        cvv: "",
        nonce: "",
        ccToken: "",

        enableDisableFields: function(disabled) {
            var fields = ["_cc_type", "_cc_number", "_expiration", "_expiration_yr", "_cc_cid"];
            var id;
            for (id = 0; id < fields.length; id++) {
                $('#' + this.options.code + fields[id]).prop('disabled', disabled);
            }
        },

        prepare : function(event, method) {
            if (method === 'braintree') {
                this.preparePayment();
            }
        },
        preparePayment: function() {
            $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
        },
        submitAdminOrder: function() {
            var ccNumber = $("#braintree_cc_number").val(),
                ccExprYr = $("#braintree_expiration_yr").val(),
                ccExprMo = $("#braintree_expiration").val(),
                self = this;
            if (self.options.useCvv) {
                var cvv = $('#braintree_cc_cid').val();
            }

            if (ccNumber) {
                this.enableDisableFields(true);
                var braintreeClient = new braintree.api.Client({clientToken: this.options.clientToken}),
                    braintreeObj = {
                        number: ccNumber,
                        expirationMonth: ccExprMo,
                        expirationYear: ccExprYr,
                        };
                if (self.options.useCvv) {
                    braintreeObj.cvv = cvv;
                }
                braintreeClient.tokenizeCard(
                    braintreeObj,
                    function (err, nonce) {
                        if (!err) {
                            $('#braintree_nonce').val(nonce);
                            if (self.options.isFraudDetectionEnabled) {
                                $('#braintree_device_id').val($('#device_data').val());
                            }
                            order._realSubmit();
                        } else {
                            //TODO: handle error case
                        }
                    }
                );
            } else {
                if (self.options.isFraudDetectionEnabled) {
                    $('#braintree_device_id').val($('#device_data').val());
                }
                order._realSubmit();
            }
        },
        useVault: function() {
            var selectBox = $('#braintree_cc_token'),
                initToken = selectBox.val(),
                self = this;

            if (initToken) {
                $('.hide_if_token_selected').hide();
                this.enableDisableFields("disabled");
                $('#braintree_nonce').val("");
            }

            $('#braintree_cc_token').bind('change', function (e) {
                var selectBox = $(this);
                var token = selectBox.val();
                if (token) {
                    $('.hide_if_token_selected').hide();
                    self.enableDisableFields(true);
                    $('#braintree_nonce').val("");
                } else {
                    $('.hide_if_token_selected').show();
                    self.enableDisableFields(false);
                }
            });
        },

        _create: function() {
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
            if (this.options.useVault) {
                this.useVault();
            }
            $('#braintree_cc_number').bind('change', function(){
                $('#cc_last4').val($("#braintree_cc_number").val().slice(-4));
            });
        }
    });

    return $.mage.braintreeCcForm;
});
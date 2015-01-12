/**
 * @category    one page checkout fifth step
 * @package     mage
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "mage/translate",
    "Magento_Checkout/js/opc-shipping-method"
], function($){
    'use strict';

    // Extension for mage.opcheckout - fifth section(Payment Information) in one page checkout accordion
    $.widget('mage.opcPaymentInfo', $.mage.opcShippingMethod, {
        options: {
            payment: {
                form: '#co-payment-form',
                continueSelector: '#opc-payment [data-role=opc-continue]',
                methodsContainer: '#checkout-payment-method-load',
                freeInput: {
                    tmpl: '<input id="hidden-free" type="hidden" name="payment[method]" value="free">',
                    selector: '#hidden-free'
                }
            }
        },

        _create: function() {
            this._super();
            var events = {};
            events['click ' + this.options.payment.continueSelector] = function() {
                if (this._validatePaymentMethod() &&
                    $(this.options.payment.form).validation &&
                    $(this.options.payment.form).validation('isValid')) {
                    this._ajaxContinue(this.options.payment.saveUrl, $(this.options.payment.form).serialize());
                }
            };
            events['contentUpdated ' + this.options.payment.form] = function() {
                $(this.options.payment.form).find('dd [name^="payment["]').prop('disabled', true);
                var checkoutPrice = this.element.find(this.options.payment.form).find('[data-checkout-price]').data('checkout-price');
                if ($.isNumeric(checkoutPrice)) {
                    this.checkoutPrice = checkoutPrice;
                }
                if (this.checkoutPrice < this.options.minBalance) {
                    this._disablePaymentMethods();
                } else {
                    this._enablePaymentMethods();
                }
            };
            events['click ' + this.options.payment.form + ' dt input:radio'] = '_paymentMethodHandler';

            $.extend(events, {
                updateCheckoutPrice: function(event, data) {
                    if (data.price) {
                        this.checkoutPrice += data.price;
                    }
                    if (data.totalPrice) {
                        data.totalPrice = this.checkoutPrice;
                    }
                    if (this.checkoutPrice < this.options.minBalance) {
                        // Add free input field, hide and disable unchecked checkbox payment method and all radio button payment methods
                        this._disablePaymentMethods();
                    } else {
                        // Remove free input field, show all payment method
                        this._enablePaymentMethods();
                    }
                }
            });

            this._on(events);

            this.element.find(this.options.payment.form).validation({
                    errorPlacement: function(error, element) {
                        if (element.attr('data-validate') && element.attr('data-validate').indexOf('validate-cc-ukss') >= 0) {
                            element.parents('form').find('[data-validation-msg="validate-cc-ukss"]').html(error);
                        } else {
                            element.after(error);
                        }
                    }
                });
        },

        /**
         * Display payment details when payment method radio button is checked
         * @private
         * @param e
         */
        _paymentMethodHandler: function(e) {
            var _this = $(e.target),
                parentsDl = _this.closest(this.options.methodsListContainer);
            parentsDl.find(this.options.methodOn).prop('checked', false);
            _this.prop('checked', true);
            parentsDl.find(this.options.methodDescription).hide().find('[name^="payment["]').prop('disabled', true);
            _this.parent().nextUntil(this.options.methodContainer).find(this.options.methodDescription).show().find('[name^="payment["]').prop('disabled', false);
        },

        /**
         * make sure one payment method is selected
         * @private
         * @return {Boolean}
         */
        _validatePaymentMethod: function() {
            var methods = this.element.find('[name^="payment["]');
            if (methods.length === 0) {
                alert($.mage.__("We can't complete your order because you don't have a payment method available."));
                return false;
            }
            if (this.checkoutPrice < this.options.minBalances) {
                return true;
            } else if (methods.filter('input:radio:checked').length) {
                return true;
            }
            alert($.mage.__('Please specify payment method.'));
            return false;
        },

        /**
         * Disable and enable payment methods
         * @private
         */
        _disablePaymentMethods: function() {
            var paymentForm = $(this.options.payment.form);
            paymentForm.find('input[name="payment[method]"]').prop('disabled', true);
            paymentForm.find(this.options.payment.methodsContainer).find('[name^="payment["]').prop('disabled', true);
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', true).parent();
            paymentForm.find(this.options.payment.freeInput.selector).remove();
            $.tmpl(this.options.payment.freeInput.tmpl).appendTo(paymentForm);
        },

        /**
         * Enable and enable payment methods
         * @private
         */
        _enablePaymentMethods: function() {
            var paymentForm = $(this.options.payment.form);
            paymentForm.find('input[name="payment[method]"]').prop('disabled', false);
            paymentForm.find('input[name="payment[method]"]:checked').trigger('click');
            paymentForm.find(this.options.payment.methodsContainer).show();
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', false).parent().show();
            paymentForm.find(this.options.payment.freeInput.selector).remove();
        }
    });
    
    return $.mage.opcPaymentInfo;
});

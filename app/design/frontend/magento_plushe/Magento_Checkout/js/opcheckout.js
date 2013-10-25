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
 * @category    one page checkout first step
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global alert*/
(function($, window) {
    'use strict';
    // Base widget, handle ajax events and first section(Checkout Method) in one page checkout accordion
    $.widget('mage.opcheckout', {
        options: {
            checkout: {
                loginGuestSelector: '#login\\:guest',
                loginRegisterSelector: '#login\\:register',
                loginFormSelector: '#login-form',
                continueSelector: '#onepage-guest-register-button',
                registerCustomerPasswordSelector: '#co-billing-form .field.password,#co-billing-form .field.confirm',
                suggestRegistration: false
            },
            sectionSelectorPrefix: '#opc-',
            billingSection: 'billing',
            ajaxLoaderPlaceButton: false,
            updateSelectorPrefix: '#checkout-',
            updateSelectorSuffix: '-load',
            backSelector: '.action.back',
            minBalance: 0.0001
        },

        _create: function() {
            var _this = this;
            this.checkoutPrice = this.options.quoteBaseGrandTotal;
            if (this.options.checkout.suggestRegistration) {
                $(this.options.checkout.loginGuestSelector).prop('checked', false);
                $(this.options.checkout.loginRegisterSelector).prop('checked', true);
            }
            this.element
                .on('click', this.options.checkout.continueSelector, function() {
                    $.proxy(_this._continue($(this)), _this);
                })
                .on('gotoSection', function(event, section) {
                    $.proxy(_this._ajaxUpdateProgress(section), _this);
                    _this.element.trigger('enableSection', {selector: _this.options.sectionSelectorPrefix + section});
                })
                .on('ajaxError', $.proxy(this._ajaxError, this))
                .on('click', this.options.backSelector, function() {
                    _this.element.trigger('enableSection', {selector: '#' + _this.element.find('.active').prev().attr('id')});
                })
                .on('click', '[data-action="login-form-submit"]', function() {
                    $(_this.options.checkout.loginFormSelector).submit();
                });
            $(this.options.checkoutProgressContainer).on('click', '[data-goto-section]', $.proxy(function(e) {
                var gotoSection = $(e.target).data('goto-section');
                this._ajaxUpdateProgress(gotoSection);
                this.element.trigger('enableSection', {selector: _this.options.sectionSelectorPrefix + gotoSection});
                return false;
            }, this));
        },

        /**
         * Callback function for before ajax send event(global)
         * @private
         */
        _ajaxSend: function() {
            this.element.addClass('loading');
            var loader = this.element.find('.section.active .please-wait').show();
            if (this.options.ajaxLoaderPlaceButton) {
                loader.siblings('.button').hide();
            }
        },

        /**
         * Callback function for ajax complete event(global)
         * @private
         */
        _ajaxComplete: function() {
            this.element.removeClass('loading');
            this.element.find('.please-wait').hide();
            if (this.options.ajaxLoaderPlaceButton) {
                this.element.find('.button').show();
            }
        },

        /**
         * ajax error for all onepage checkout ajax calls
         * @private
         */
        _ajaxError: function() {
            window.location.href = this.options.failureUrl;
        },

        /**
         * callback function when continue button is clicked
         * @private
         * @param elem - continue button
         * @return {Boolean}
         */
        _continue: function(elem) {
            var json = elem.data('checkout');
            if (json.isGuestCheckoutAllowed) {
                if ($(this.options.checkout.loginGuestSelector).is(':checked')) {
                    this._ajaxContinue(this.options.checkout.saveUrl, {method:'guest'}, this.options.billingSection);
                    this.element.find(this.options.checkout.registerCustomerPasswordSelector).hide();
                } else if ($(this.options.checkout.loginRegisterSelector).is(':checked')) {
                    this._ajaxContinue(this.options.checkout.saveUrl, {method:'register'}, this.options.billingSection);
                    this.element.find(this.options.checkout.registerCustomerPasswordSelector).show();
                } else {
                    alert($.mage.__('Please choose to register or to checkout as a guest'));
                }
            }
            return false;
        },

        /**
         * Ajax call to save checkout info to backend and enable next section in accordion
         * @private
         * @param url - ajax url
         * @param data - post data for ajax call
         * @param gotoSection - the section needs to show after ajax call
         * @param successCallback - custom callback function in ajax success
         */
        _ajaxContinue: function(url, data, gotoSection, successCallback) {
            $.ajax({
                url: url,
                type: 'post',
                context: this,
                data: data,
                dataType: 'json',
                beforeSend: this._ajaxSend,
                complete: this._ajaxComplete,
                success: function(response) {
                    if (successCallback) {
                        successCallback.call(this, response);
                    }
                    if ($.type(response) === 'object' && !$.isEmptyObject(response)) {
                        if (response.error) {
                            var msg = response.message;
                            if (msg) {
                                if ($.type(msg) === 'array') {
                                    msg = msg.join("\n");
                                }
                                $(this.options.countrySelector).trigger('change');
                                alert($.mage.__(msg));
                            } else {
                                alert($.mage.__(response.error));
                            }
                            return;
                        }
                        if (response.redirect) {
                            $.mage.redirect(response.redirect);
                            return false;
                        }
                        else if (response.success) {
                            $.mage.redirect(this.options.review.successUrl);
                            return false;
                        }
                        if (response.update_section) {
                            if (response.update_section.name === 'payment-method' && response.update_section.html.indexOf('data-checkout-price')) {
                                this.element.find(this.options.payment.form).find('[data-checkout-price]').remove();
                            }
                            $(this.options.updateSelectorPrefix + response.update_section.name + this.options.updateSelectorSuffix)
                                .html($(response.update_section.html)).trigger('contentUpdated');
                        }
                        if (response.duplicateBillingInfo) {
                            $(this.options.shipping.copyBillingSelector).prop('checked', true).trigger('click');
                            $(this.options.shipping.addressDropdownSelector).val($(this.options.billing.addressDropdownSelector).val()).change();
                        }
                        if (response.goto_section) {
                            this.element.trigger('gotoSection', response.goto_section);
                        }
                    } else {
                        this.element.trigger('gotoSection', gotoSection);
                    }
                }
            });
        },

        /**
         * Update progress sidebar content
         * @private
         * @param toStep
         */
        _ajaxUpdateProgress: function(toStep) {
            if (toStep) {
                $.ajax({
                    url: this.options.progressUrl,
                    type: 'get',
                    async: false,
                    cache: false,
                    context: this,
                    data: toStep ? {toStep: toStep} : null,
                    success: function(response) {
                        $(this.options.checkoutProgressContainer).html(response);
                    }
                });
            }
        }
    });

    // Extension for mage.opcheckout - second section(Billing Information) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            billing: {
                addressDropdownSelector: '#billing-address-select',
                newAddressFormSelector: '#billing-new-address-form',
                continueSelector: '#billing-buttons-container .button',
                form: '#co-billing-form'
            }
        },

        _create: function() {
            this._super();
            this.element
                .on('change', this.options.billing.addressDropdownSelector, $.proxy(function(e) {
                    this.element.find(this.options.billing.newAddressFormSelector).toggle(!$(e.target).val());
                }, this))
                .on('click', this.options.billing.continueSelector, $.proxy(function() {
                    if ($(this.options.billing.form).validation && $(this.options.billing.form).validation('isValid')) {
                        this._billingSave();
                    }
                }, this))
                .find(this.options.billing.form).validation();
        } ,

        _billingSave: function() {
            this._ajaxContinue(this.options.billing.saveUrl, $(this.options.billing.form).serialize(), false, function() {
                //Trigger indicating billing save. eg. GiftMessage listens to this to inject gift options
                this.element.trigger('billingSave');
            });
        }
    });

    // Extension for mage.opcheckout - third section(Shipping Information) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            shipping: {
                form: '#co-shipping-form',
                addressDropdownSelector: '#shipping-address-select',
                newAddressFormSelector: '#shipping-new-address-form',
                copyBillingSelector: '#shipping\\:same_as_billing',
                countrySelector: '#shipping\\:country_id',
                continueSelector:'#shipping-buttons-container .button'
            }
        },

        _create: function() {
            this._super();
            this.element
                .on('change', this.options.shipping.addressDropdownSelector, $.proxy(function(e) {
                    $(this.options.shipping.newAddressFormSelector).toggle(!$(e.target).val());
                }, this))
                .on('input propertychange', this.options.shipping.form + ' :input[name]', $.proxy(function() {
                    $(this.options.shipping.copyBillingSelector).prop('checked', false);
                }, this))
                .on('click', this.options.shipping.copyBillingSelector, $.proxy(function(e) {
                    if ($(e.target).is(':checked')) {
                        this._billingToShipping();
                    }
                }, this))
                .on('click', this.options.shipping.continueSelector, $.proxy(function() {
                    if ($(this.options.shipping.form).validation && $(this.options.shipping.form).validation('isValid')) {
                    this._ajaxContinue(this.options.shipping.saveUrl, $(this.options.shipping.form).serialize(), false, function() {
                        //Trigger indicating shipping save. eg. GiftMessage listens to this to inject gift options
                        this.element.trigger('shippingSave');
                    });
                    }
                }, this))
                .find(this.options.shipping.form).validation();
        },

        /**
         * Copy billing address info to shipping address
         * @private
         */
        _billingToShipping: function() {
            $(':input[name]', this.options.billing.form).each($.proxy(function(key, value) {
                var fieldObj = $(value.id.replace('billing:', '#shipping\\:'));
                fieldObj.val($(value).val());
                if (fieldObj.is("select")) {
                    fieldObj.trigger('change');
                }
            }, this));
            $(this.options.shipping.copyBillingSelector).prop('checked', true);
        }
    });

    // Extension for mage.opcheckout - fourth section(Shipping Method) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            shippingMethod: {
                continueSelector: '#shipping-method-buttons-container .button',
                form: '#co-shipping-method-form'
            }
        },

        _create: function() {
            this._super();
            var _this = this;
            this.element
                .on('click', this.options.shippingMethod.continueSelector, $.proxy(function() {
                    if (this._validateShippingMethod()&&
                        $(this.options.shippingMethod.form).validation &&
                        $(this.options.shippingMethod.form).validation('isValid')) {
                        this._ajaxContinue(this.options.shippingMethod.saveUrl, $(this.options.shippingMethod.form).serialize());
                    }
                }, this))
                .on('click', 'input[name="shipping_method"]', function() {
                    var selectedPrice = _this.shippingCodePrice[$(this).val()] || 0,
                        oldPrice = _this.shippingCodePrice[_this.currentShippingMethod] || 0;
                    _this.checkoutPrice = _this.checkoutPrice - oldPrice + selectedPrice;
                    _this.currentShippingMethod = $(this).val();
                })
                .on('contentUpdated', $.proxy(function() {
                    this.currentShippingMethod = this.element.find('input[name="shipping_method"]:checked').val();
                    this.shippingCodePrice = this.element.find('[data-shipping-code-price]').data('shipping-code-price');
                }, this))
                .find(this.options.shippingMethod.form).validation();
        },

        /**
         * Make sure at least one shipping method is selected
         * @return {Boolean}
         * @private
         */
        _validateShippingMethod: function() {
            var methods = this.element.find('[name="shipping_method"]');
            if (methods.length === 0) {
                alert($.mage.__('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
                return false;
            }
            if (methods.filter(':checked').length) {
                return true;
            }
            alert($.mage.__('Please specify shipping method.'));
            return false;
        }
    });

    // Extension for mage.opcheckout - fifth section(Payment Information) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            payment: {
                continueSelector: '#payment-buttons-container .button',
                form: '#co-payment-form',
                methodsContainer: '#checkout-payment-method-load',
                freeInput: {
                    tmpl: '<input id="hidden-free" type="hidden" name="payment[method]" value="free">',
                    selector: '#hidden-free'
                }
            }
        },

        _create: function() {
            this._super();
            this.element
                .on('click', this.options.payment.continueSelector, $.proxy(function() {
                    if (this._validatePaymentMethod() &&
                        $(this.options.payment.form).validation &&
                        $(this.options.payment.form).validation('isValid')) {
                        this._ajaxContinue(this.options.payment.saveUrl, $(this.options.payment.form).serialize());
                    }
                }, this))
                .on('updateCheckoutPrice', $.proxy(function(event, data) {
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
                }, this))
                .on('contentUpdated', this.options.payment.form, $.proxy(function() {
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
                }, this))
                .on('click', this.options.payment.form + ' dt input:radio', $.proxy(this._paymentMethodHandler, this))
                .find(this.options.payment.form).validation();
        },

        /**
         * Display payment details when payment method radio button is checked
         * @private
         * @param e
         */
        _paymentMethodHandler: function(e) {
            var _this = $(e.target),
                parentsDl = _this.closest('dl');
            parentsDl.find('dt input:radio').prop('checked', false);
            _this.prop('checked', true);
            parentsDl.find('dd ul').hide().find('[name^="payment["]').prop('disabled', true);
            _this.parent().nextUntil('dt').find('ul').show().find('[name^="payment["]').prop('disabled', false);
        },

        /**
         * make sure one payment method is selected
         * @private
         * @return {Boolean}
         */
        _validatePaymentMethod: function() {
            var methods = this.element.find('[name^="payment["]');
            if (methods.length === 0) {
                alert($.mage.__('Your order cannot be completed at this time as there is no payment methods available for it.'));
                return false;
            }
            if (this.checkoutPrice < this.options.minBalance) {
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
            paymentForm.find(this.options.payment.methodsContainer).hide().find('[name^="payment["]').prop('disabled', true);
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', true).parent().hide();
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
            paymentForm.find(this.options.payment.methodsContainer).show();
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', false).parent().show();
            paymentForm.find(this.options.payment.freeInput.selector).remove();
        }
    });

    // Extension for mage.opcheckout - last section(Order Review) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            review: {
                continueSelector: '#review-buttons-container .button'
            }
        },

        _create: function() {
            this._super();
            this.element
                .on('click', this.options.review.continueSelector, $.proxy(function() {
                    if ($(this.options.payment.form).validation &&
                        $(this.options.payment.form).validation('isValid')) {
                        this._ajaxContinue(
                            this.options.review.saveUrl,
                            $(this.options.payment.form).serialize());
                    }
                }, this));
        }
    });
})(jQuery, window);

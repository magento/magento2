/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/validation",
    "jquery/template",
    "mage/translate"
], function($){
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
            minBalance: 0.0001,
            methodsListContainer: 'dl',
            methodContainer: 'dt',
            methodDescription : 'dd ul',
            methodOn: 'dt input:radio'
        },

        _create: function() {
            this.checkoutPrice = this.options.quoteBaseGrandTotal;
            if (this.options.checkout.suggestRegistration) {
                $(this.options.checkout.loginGuestSelector).prop('checked', false);
                $(this.options.checkout.loginRegisterSelector).prop('checked', true);
            }
            var events = {};
            events['click ' + this.options.checkout.continueSelector] = function(e) {
                this._continue($(e.currentTarget));
            };
            events['click ' + this.options.backSelector] = function() {
                this.element.trigger('enableSection', {selector: '#' + this.element.find('.active').prev().attr('id')});
            };

            $(document).on({
                'ajaxError': this._ajaxError.bind(this)
            });

            $.extend(events, {
                showAjaxLoader: '_ajaxSend',
                hideAjaxLoader: '_ajaxComplete',
                gotoSection: function(e, section) {
                    this._ajaxUpdateProgress(section);
                    this.element.trigger('enableSection', {selector: this.options.sectionSelectorPrefix + section});
                },
                'click [data-action=login-form-submit]': function() {
                    $(this.options.checkout.loginFormSelector).submit();
                }
            });
            this._on(events);

            this._on($(this.options.checkoutProgressContainer), {
                'click [data-goto-section]' : function(e) {
                    var gotoSection = $(e.target).data('goto-section');
                    this._ajaxUpdateProgress(gotoSection);
                    this.element.trigger('enableSection', {selector: this.options.sectionSelectorPrefix + gotoSection});
                    return false;
                }
            });
        },

        /**
         * Callback function for before ajax send event(global)
         * @private
         */
        _ajaxSend: function() {
            this.element.addClass('loading');
            var loader = this.element.find('.please-wait').show();
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
                    alert($.mage.__('Please choose to register or to checkout as a guest.'));
                    return false;
                }
            } else {
                if (json.registrationUrl) {
                    window.location.href = json.registrationUrl;
                }
            }
            this.element.trigger('login');
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
                            var msg = response.message || response.error_messages;
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
                addressDropdownSelector: '#billing\\:address-select',
                newAddressFormSelector: '#billing-new-address-form',
                continueSelector: '#billing-buttons-container .button',
                form: '#co-billing-form'
            }
        },

        _create: function() {
            this._super();
            var events = {};
            events['change ' + this.options.billing.addressDropdownSelector] = function(e) {
                this.element.find(this.options.billing.newAddressFormSelector).toggle(!$(e.target).val());
            };
            events['click ' + this.options.billing.continueSelector] = function() {
                if ($(this.options.billing.form).validation && $(this.options.billing.form).validation('isValid')) {
                    this._billingSave();
                }
            };
            this._on(events);

            this.element.find(this.options.billing.form).validation();
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
                addressDropdownSelector: '#shipping\\:address-select',
                newAddressFormSelector: '#shipping-new-address-form',
                copyBillingSelector: '#shipping\\:same_as_billing',
                countrySelector: '#shipping\\:country_id',
                continueSelector:'#shipping-buttons-container .button'
            }
        },

        _create: function() {
            this._super();
            var events = {};
            var onInputPropChange = function() {
                $(this.options.shipping.copyBillingSelector).prop('checked', false);
            };
            events['change ' + this.options.shipping.addressDropdownSelector] = function(e) {
                $(this.options.shipping.newAddressFormSelector).toggle(!$(e.target).val());
                onInputPropChange.call(this);
            };
            // for guest checkout
            events['input ' + this.options.shipping.form + ' :input[name]'] = onInputPropChange;
            events['propertychange ' + this.options.shipping.form + ' :input[name]'] = onInputPropChange;
            events['click ' + this.options.shipping.copyBillingSelector] = function(e) {
                if ($(e.target).is(':checked')) {
                    this._billingToShipping();
                }
            };
            events['click ' + this.options.shipping.continueSelector] = function() {
                if ($(this.options.shipping.form).validation && $(this.options.shipping.form).validation('isValid')) {
                    this._ajaxContinue(this.options.shipping.saveUrl, $(this.options.shipping.form).serialize(), false, function() {
                        //Trigger indicating shipping save. eg. GiftMessage listens to this to inject gift options
                        this.element.trigger('shippingSave');
                    });
                }
            };
            this._on(events);

            this.element.find(this.options.shipping.form).validation();
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
            var events = {};
            events['click ' + this.options.shippingMethod.continueSelector] = function() {
                if (this._validateShippingMethod()&&
                    $(this.options.shippingMethod.form).validation &&
                    $(this.options.shippingMethod.form).validation('isValid')) {
                    this._ajaxContinue(this.options.shippingMethod.saveUrl, $(this.options.shippingMethod.form).serialize());
                }
            };
            $.extend(events, {
                'click input[name=shipping_method]': function(e) {
                    var selectedPrice = this.shippingCodePrice[$(e.target).val()] || 0,
                        oldPrice = this.shippingCodePrice[this.currentShippingMethod] || 0;
                    this.checkoutPrice = this.checkoutPrice - oldPrice + selectedPrice;
                    this.currentShippingMethod = $(e.target).val();
                },
                'contentUpdated': function() {
                    this.currentShippingMethod = this.element.find('input[name="shipping_method"]:checked').val();
                    this.shippingCodePrice = this.element.find('[data-shipping-code-price]').data('shipping-code-price');
                }
            });
            this._on(events);

            this.element.find(this.options.shippingMethod.form).validation();
        },

        /**
         * Make sure at least one shipping method is selected
         * @return {Boolean}
         * @private
         */
        _validateShippingMethod: function() {
            var methods = this.element.find('[name="shipping_method"]');
            if (methods.length === 0) {
                alert($.mage.__('We are not able to ship to the selected shipping address. Please choose another address or edit the current address.'));
                return false;
            }
            if (methods.filter(':checked').length) {
                return true;
            }
            alert($.mage.__('Please specify a shipping method.'));
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
            _this.closest(this.options.methodContainer)
                .nextUntil(this.options.methodContainer)
                .find(this.options.methodDescription).show().find('[name^="payment["]').prop('disabled', false);
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

    // Extension for mage.opcheckout - last section(Order Review) in one page checkout accordion
    $.widget('mage.opcheckout', $.mage.opcheckout, {
        options: {
            review: {
                continueSelector: '#review-buttons-container .button',
                container: '#opc-review',
                agreementFormSelector:'#checkout-agreements input[type="checkbox"]',
            }
        },

        _create: function() {
            this._super();
            var events = {};
            events['click ' + this.options.review.continueSelector] = this._saveOrder;
            events['saveOrder' + this.options.review.container] = this._saveOrder;
            this._on(events);
        },

        _saveOrder: function() {
            if ($(this.options.payment.form).validation &&
                $(this.options.payment.form).validation('isValid')) {
                this._ajaxContinue(
                    this.options.review.saveUrl,
                    $(this.options.payment.form).serialize() + '&' + $(this.options.review.agreementFormSelector).serialize());
            }
        }
    });

});

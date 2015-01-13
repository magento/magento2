/**
 * @category    one page checkout first step
 * @package     mage
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/validation/validation",
    "Magento_Checkout/js/accordion",
    "mage/translate"
], function($){
    'use strict';

    // Base widget, handle ajax events and first section(Checkout Method) in one page checkout accordion
    $.widget('mage.opcCheckoutMethod', {
        options: {
            checkout: {
                loginGuestSelector: '[data-role=checkout-method-guest]',
                loginRegisterSelector: '[data-role=checkout-method-register]',
                loginFormSelector: 'form[data-role=login]',
                continueSelector: '#opc-login [data-role=opc-continue]',
                registerCustomerPasswordSelector: '#co-billing-form .field.password,#co-billing-form .field.confirm',
                captchaGuestCheckoutSelector: '#co-billing-form [role="guest_checkout"]',
                registerDuringCheckoutSelector: '#co-billing-form [role="register_during_checkout"]',
                suggestRegistration: false
            },
            pageMessages: '#maincontent .messages .message',
            sectionSelectorPrefix: 'opc-',
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
            var self = this;
            this.sectionActiveClass = this.element.accordion("option","openedState");
            this.contentSelector = this.element.accordion("option","content");
            this.checkoutPrice = this.options.quoteBaseGrandTotal;
            if (this.options.checkout.suggestRegistration) {
                $(this.options.checkout.loginGuestSelector).prop('checked', false);
                $(this.options.checkout.loginRegisterSelector).prop('checked', true);
            }
            this._handleSteps();
            var events = {};
            events['click ' + this.options.checkout.continueSelector] = function(e) {
                this._continue($(e.currentTarget));
            };
            events['click ' + this.options.backSelector] = function(event) {
                event.preventDefault();
                var prev  = self.steps.index($('li.' + self.sectionActiveClass)) -1 ;
                this._activateSection(prev);
            };
            events['click ' + '[data-action=checkout-method-login]'] = function(event) {
                if($(self.options.checkout.loginFormSelector).validation('isValid')){
                    self.element.find('.section').filter('.' + self.sectionActiveClass).children(self.contentSelector).trigger("processStart");
                    event.preventDefault();
                    setTimeout(function(){
                        $(self.options.checkout.loginFormSelector).submit();
                    }, 300);
                }
            };

            $(document).on({
                'ajaxError': this._ajaxError.bind(this)
            });

            $.extend(events, {
                showAjaxLoader: '_ajaxSend',
                hideAjaxLoader: '_ajaxComplete',
                gotoSection: function(e, section) {
                    self.element.find('.section').filter('.' + self.sectionActiveClass).children(self.contentSelector).trigger("processStop");
                    var toActivate = this.steps.index($('#' + self.options.sectionSelectorPrefix + section));
                    this._activateSection(toActivate);
                }
            });
            this._on(events);

            this._on($(this.options.checkoutProgressContainer), {
                'click [data-goto-section]' : function(e) {
                    var gotoSection = $(e.target).data('goto-section');
                    self.element.find('.section').filter('.' + self.sectionActiveClass).children(self.contentSelector).trigger("processStop");
                    var toActivate = this.steps.index($('#' + self.options.sectionSelectorPrefix + gotoSection));
                    this._activateSection(toActivate);
                    return false;
                }
            });
        },

        /**
         * Get the checkout steps, disable steps but first, adding callback on before opening section to
         * disable all next steps
         * @private
         */
        _handleSteps: function() {
            var self = this;
            this.steps = $(this.element).children('[id^=' + this.options.sectionSelectorPrefix + ']');
            this.element.accordion("disable");
            this._activateSection(0);
            $.each(this.steps,function() {
                $(this).on("beforeOpen",function() {
                    $(this).nextAll('[id^=' + self.options.sectionSelectorPrefix + ']').collapsible("disable");
                    $(this).prevAll('[id^=' + self.options.sectionSelectorPrefix + ']').collapsible("enable");
                });
            });
        },

        /**
         * Activate section
         * @param index the index of section you want to open
         * @private
         */
        _activateSection: function(index) {
            this.element.accordion("enable",index);
            this.element.accordion("activate",index);
        },

        /**
         * Callback function for before ajax send event(global)
         * @private
         */
        _ajaxSend: function() {
            this.element.find('.section').filter('.' + this.sectionActiveClass).children(this.contentSelector).trigger("processStart");
        },

        /**
         * Callback function for ajax complete event(global)
         * @private
         */
        _ajaxComplete: function() {
            this.element.find('.section').filter('.' + this.sectionActiveClass).children(this.contentSelector).trigger("processStop");
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
            var json            = elem.data('checkout'),
                checkout        = this.options.checkout,
                guestChecked    = $( checkout.loginGuestSelector ).is( ':checked' ),
                registerChecked = $( checkout.loginRegisterSelector ).is( ':checked' ),
                method          = 'register',
                isRegistration  = true;

            //Remove page messages
            $(this.options.pageMessages).remove();
            
            if (json.isGuestCheckoutAllowed) {
                
                if( !guestChecked && !registerChecked ){
                    alert( $.mage.__('Please choose to register or to checkout as a guest.') );
                    
                    return false;
                }

                if( guestChecked ){
                    method = 'guest';
                    isRegistration = false;
                }

                this._ajaxContinue(
                    checkout.saveUrl,
                    { method: method },
                    this.options.billingSection
                );

                this.element.find(checkout.registerCustomerPasswordSelector).toggle(isRegistration);
                this.element.find(checkout.captchaGuestCheckoutSelector).toggle(!isRegistration);
                this.element.find(checkout.registerDuringCheckoutSelector).toggle(isRegistration);
            }
            else if( json.registrationUrl ){
                window.location = json.registrationUrl;
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
                success: function (response) {
                    if (successCallback) {
                        successCallback.call(this, response);
                    }
                    if ($.type(response) === 'object' && !$.isEmptyObject(response)) {
                        if (response.error) {
                            var msg = response.message || response.error_messages || response.error;

                            if (msg) {
                                if (Array.isArray(msg)) {
                                    msg = msg.reduce(function (str, chunk) {
                                        str += '\n' + $.mage.__(chunk);
                                        return str;
                                    }, '');
                                } else {
                                    msg = $.mage.__(msg);
                                }

                                $(this.options.countrySelector).trigger('change');

                                alert(msg);
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
                        if (response.update_progress) {
                            $(this.options.checkoutProgressContainer).html($(response.update_progress.html)).trigger('progressUpdated');
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
        }
    });
    
    return $.mage.opcCheckoutMethod;
});

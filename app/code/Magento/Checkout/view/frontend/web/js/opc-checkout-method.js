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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
            $.extend(events, {
                ajaxError: '_ajaxError',
                showAjaxLoader: '_ajaxSend',
                hideAjaxLoader: '_ajaxComplete',
                gotoSection: function(e, section) {
                    this._ajaxUpdateProgress(section);
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
                    this._ajaxUpdateProgress(gotoSection);
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
                action          = 'show';

            //Remove page messages
            $(this.options.pageMessages).remove();
            
            if (json.isGuestCheckoutAllowed) {
                
                if( !guestChecked && !registerChecked ){
                    alert( $.mage.__('Please choose to register or to checkout as a guest.') );
                    
                    return false;
                }

                if( guestChecked ){
                    method = 'guest';
                    action = 'hide';
                }

                this._ajaxContinue(
                    checkout.saveUrl,
                    { method: method },
                    this.options.billingSection
                );

                this.element.find( checkout.registerCustomerPasswordSelector )[action]();
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
                                var emailAddress = {};
                                emailAddress[this.options.billing.emailAddressName] = msg;
                                var billingFormValidator = $( this.options.billing.form ).validate();
                                billingFormValidator.showErrors(emailAddress);
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
});

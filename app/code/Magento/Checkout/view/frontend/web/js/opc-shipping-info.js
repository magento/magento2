/**
 * @category    one page checkout third step
 * @package     mage
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "Magento_Checkout/js/opc-billing-info",
    "mage/validation"
], function($){
    'use strict';

    // Extension for mage.opcheckout - third section(Shipping Information) in one page checkout accordion
    $.widget('mage.opcShippingInfo', $.mage.opcBillingInfo, {
        options: {
            shipping: {
                form: '#co-shipping-form',
                continueSelector:'#opc-shipping [data-role=opc-continue]',
                addressDropdownSelector: '#shipping\\:address-select',
                newAddressFormSelector: '#shipping-new-address-form',
                copyBillingSelector: '#shipping\\:same_as_billing',
                countrySelector: '#shipping\\:country_id'
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
    
    return $.mage.opcShippingInfo;
});
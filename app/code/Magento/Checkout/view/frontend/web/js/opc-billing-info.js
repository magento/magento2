/**
 * @category    one page checkout second step
 * @package     mage
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "Magento_Checkout/js/opc-checkout-method",
    "mage/validation"
], function($){
    'use strict';
       
    // Extension for mage.opcheckout - second section(Billing Information) in one page checkout accordion
    $.widget('mage.opcBillingInfo', $.mage.opcCheckoutMethod, {
        options: {
            billing: {
                form: '#co-billing-form',
                continueSelector: '#opc-billing [data-role=opc-continue]',
                addressDropdownSelector: '#billing\\:address-select',
                newAddressFormSelector: '#billing-new-address-form',
                emailAddressName: 'billing[email]'
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
    
    return $.mage.opcBillingInfo;
});
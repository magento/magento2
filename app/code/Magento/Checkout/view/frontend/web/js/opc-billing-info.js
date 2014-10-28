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
 * @category    one page checkout second step
 * @package     mage
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
                addressDropdownSelector: '#billing-address-select',
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

});
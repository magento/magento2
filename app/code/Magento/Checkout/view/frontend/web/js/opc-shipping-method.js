/**
 * @category    one page checkout fourth step
 * @package     mage
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
/**
 * @deprecated
 * @removeCandidate
 */
define([
    "jquery",
    "jquery/ui",
    "Magento_Checkout/js/opc-shipping-info",
    "mage/validation",
    "mage/translate"
], function($){
    'use strict';    

    // Extension for mage.opcheckout - fourth section(Shipping Method) in one page checkout accordion
    $.widget('mage.opcShippingMethod', $.mage.opcShippingInfo, {
        options: {
            shippingMethod: {
                form: '#co-shipping-method-form',
                continueSelector: '#opc-shipping_method [data-role=opc-continue]'
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
                alert($.mage.__('We can\'t ship to this address. Please enter another address or edit this one.'));
                return false;
            }
            if (methods.filter(':checked').length) {
                return true;
            }
            alert($.mage.__('Please specify a shipping method.'));
            return false;
        }
    });
    
    return $.mage.opcShippingMethod;
});
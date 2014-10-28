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
 * @category    one page checkout fourth step
 * @package     mage
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global alert*/
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

});
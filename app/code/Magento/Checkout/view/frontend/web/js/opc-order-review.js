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
 * @category    one page checkout last step
 * @package     mage
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "Magento_Checkout/js/opc-payment-info"
], function($){
    'use strict';

    // Extension for mage.opcheckout - last section(Order Review) in one page checkout accordion
    $.widget('mage.opcOrderReview', $.mage.opcPaymentInfo, {
        options: {
            review: {
                continueSelector: '#opc-review [data-role=review-save]',
                container: '#opc-review',
                agreementGroupSelector: '#checkout-agreements'
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
            var agreementFormsGroup = $(this.options.review.agreementGroupSelector),
                paymentForm = $(this.options.payment.form);
            var isAgreementValid = true;
            agreementFormsGroup.find('form').each(
                function(){
                    $(this).validation();
                    isAgreementValid = isAgreementValid && $(this).validation && $(this).validation('isValid');
                }
            );

            if (isAgreementValid &&
                paymentForm.validation &&
                paymentForm.validation('isValid')) {
                var serializedAgreement = '';
                agreementFormsGroup.find('form').each(function(){serializedAgreement += '&' + $(this).serialize();});
                this._ajaxContinue(
                    this.options.review.saveUrl,
                    paymentForm.serialize() + serializedAgreement);
            }
        }
    });

});

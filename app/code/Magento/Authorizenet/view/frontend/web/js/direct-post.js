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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global alert:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){
    "use strict";
    
    $.widget('mage.directpost', {
        options: {
            placeOrderSelector: '[data-role="review-save"]',
            paymentFormSelector: '#co-payment-form',
            updateSelectorPrefix: '#checkout-',
            updateSelectorSuffix: '-load',
            ccNumberSelector: '[data-container="cc-number"]',
            ccMonthSelector: '[data-container="cc-month"]',
            ccYearSelector: '[data-container="cc-year"]',
            ccCvvSelector: '[data-container="cc-cvv"]',
            iframeSelector: '[data-container="authorize-net-iframe"]',
            hiddenFormTmpl: '<form target="${target}" action="${action}" method="POST" enctype="application/x-www-form-urlencoded" class="no-display">' +
                            '{{each(key, val) inputs}} <input value="${val}" name="${key}" type="hidden"> {{/each}}' +
                            '</form>',
            reviewAgreementForm: '#checkout-agreements',
            cgiUrl: null,
            orderSaveUrl: null,
            controller: null
        },

        _create: function() {
            $(this.options.placeOrderSelector)
                .off('click')
                .on('click', $.proxy(this._placeOrderHandler, this));
        },

        /**
         * handler for Place Order button to call authorize.net for credit card validation
         * @return {Boolean}
         * @private
         */
        _placeOrderHandler: function() {
            if (this.element.validation && this.element.validation('isValid')) {
                this._orderSave();
            }
            return false;
        },

        /**
         * Save order and generate post data for authorize.net call
         * @private
         */
        _orderSave: function() {
            var postData = $(this.options.paymentFormSelector).serialize();
            if ($(this.options.reviewAgreementForm).length) {
                postData += '&' + $(this.options.reviewAgreementForm).serialize();
            }
            postData += '&controller=' + this.options.controller;
            $.ajax({
                url: this.options.orderSaveUrl,
                type: 'post',
                context: this,
                data: postData,
                dataType: 'json',
                beforeSend: function() {this.element.trigger('showAjaxLoader');},
                complete: function() {this.element.trigger('hideAjaxLoader');},
                success: function(response) {
                    if (response.success && response.directpost) {
                        var preparedData = this._preparePaymentData(response.directpost.fields);
                        this._postPaymentToAuthorizeNet(preparedData);
                    } else {
                        var msg = response.error_messages;
                        if (typeof (msg) === 'object') {
                            msg = msg.join("\n");
                        }
                        if (msg) {
                            alert(msg);
                        }
                        if (response.update_section) {
                            $(this.options.updateSelectorPrefix + response.update_section.name + this.options.updateSelectorSuffix)
                                .html($(response.update_section.html)).trigger('contentUpdated');
                        }
                        if (response.goto_section) {
                            this.element.trigger('gotoSection', response.goto_section);
                        }
                    }
                }
            });
        },

        /**
         * Post data to auhtorize.net for credit card validation
         * @param data
         * @private
         */
        _postPaymentToAuthorizeNet: function(data) {
            $(this.options.iframeSelector).show();
            $.template('hiddenFormTmpl', this.options.hiddenFormTmpl);
            $.tmpl('hiddenFormTmpl', {
                target: $(this.options.iframeSelector).attr('name'),
                action: this.options.cgiUrl,
                inputs: data
            }).appendTo('body').submit();
        },

        /**
         * Add credit card fields to post data for authorize.net
         * @param data
         * @private
         */
        _preparePaymentData: function(data) {
            var year = this.element.find(this.options.ccYearSelector).val(),
                month = parseInt(this.element.find(this.options.ccMonthSelector).val(), 10);
            if (year.length > 2) {
                year = year.substring(2);
            }
            if (this.element.find(this.options.ccCvvSelector).length) {
                data.x_card_code = this.element.find(this.options.ccCvvSelector).val();
            }
            if (month < 10) {
                month = '0' + month;
            }
            data.x_exp_date = month + '/' + year;
            data.x_card_num = this.element.find(this.options.ccNumberSelector).val();
            return data;
        }
    });


});
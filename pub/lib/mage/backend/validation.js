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
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true browser:true*/
/*global BASE_URL:true*/
(function($) {
    var init = $.validator.prototype.init;
    $.extend(true, $.validator.prototype, {
        /**
         * validator initialization
         */
        init: function() {
            init.apply(this, arguments);
            var highlight = this.settings.highlight;
            this.settings.highlight = function (element) {
                highlight.apply(this, arguments);
                $(element).trigger('highlight.validate');
            };
        },

        /**
         * Focus invalid fields
         */
        focusInvalid: function() {
            if (this.settings.focusInvalid) {
                try {
                    $(this.errorList.length && this.errorList[0].element || [])
                        .focus()
                        .trigger("focusin");
                } catch (e) {
                    // ignore IE throwing errors when focusing hidden elements
                }
            }
        }
    });

    $.widget("mage.validation", $.mage.validation, {
        options: {
            messagesId: 'messages',
            ignore: ':disabled, .ignore-validate, .no-display.template, ' +
                ':disabled input, .ignore-validate input, .no-display.template input, ' +
                ':disabled select, .ignore-validate select, .no-display.template select, ' +
                ':disabled textarea, .ignore-validate textarea, .no-display.template textarea',
            errorElement: 'label',
            errorUrl: typeof BASE_URL !== 'undefined' ? BASE_URL : null
        },

        /**
         * Validation creation
         * @protected
         */
        _create: function() {
            if (!this.options.submitHandler && $.type(this.options.submitHandler) !== 'function') {
                if (!this.options.frontendOnly && this.options.validationUrl) {
                    this.options.submitHandler = $.proxy(this._ajaxValidate, this);
                } else {
                    this.options.submitHandler = $.proxy(this.element[0].submit, this.element[0]);
                }
            }
            this.element.on('resetElement', function(e) {$(e.target).rules('remove');});
            this._super('_create');
        },

        /**
         * ajax validation
         * @protected
         */
        _ajaxValidate: function() {
            $.ajax({
                url: this.options.validationUrl,
                type: 'POST',
                dataType: 'json',
                data: this.element.serialize(),
                context: $('body'),
                success: $.proxy(this._onSuccess, this),
                error: $.proxy(this._onError, this),
                showLoader: true
            });
        },

        /*
         * Process ajax success
         * @protected
         * @param {Object} JSON-response
         * @param {string} response status
         * @param {Object} The jQuery XMLHttpRequest object returned by $.ajax()
         */
        _onSuccess: function(response) {
            if (response.attribute) {
                $('#' + response.attribute)
                    .addClass('validate-ajax-error')
                    .data('msg-validate-ajax-error', response.message);
                this.validate.element( "#" + response.attribute);
            }
            if (!response.error) {
                this.element[0].submit();
            }
        },

        /*
         * Process ajax error
         * @protected
         */
        _onError: function() {
            if (this.options.errorUrl) {
                location.href = this.options.errorUrl;
            }
        }
    });
})(jQuery);

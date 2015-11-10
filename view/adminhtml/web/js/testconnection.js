/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.testConnection', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: '',
            fieldMapping: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._connect, this)
            });
        },

        /**
         * Method triggers an AJAX request to check search engine connection
         * @private
         */
        _connect: function () {
            var result = this.options.failedText;
            var element =  $('#' + this.options.elementId);
            var self = this;
            var params = {};
            var msg = '';

            element.removeClass('success').addClass('fail');
            $.each($.parseJSON(this.options.fieldMapping), function (key, element) {
                params[key]= $('#' + element).val();
            });
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: params
            }).done(function (response) {
                if (response.success) {
                    element.removeClass('fail').addClass('success');
                    result = self.options.successText;
                } else {
                    msg = response.errorMessage;

                    if (msg) {
                        alert({
                            content: $.mage.__(msg)
                        });
                    }
                }
            }).always(function () {
                $('#' + self.options.elementId + '_result').text(result);
            });
        }
    });

    return $.mage.testConnection;
});

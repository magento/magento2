/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/form/form',
    'mage/translate'
], function ($, uiAlert, Form, $t) {
    'use strict';

    return Form.extend({
        defaults: {
            ajaxSettings: {
              method: 'POST',
              dataType: 'json'
            }
        },

        /**
         * Perform asynchronous DELETE request to server.
         * @param {String} url - ajax url
         * @returns {Deferred}
         */
        delete: function (url) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: url,
                data: {
                    'form_key': window.FORM_KEY
                }
            });

            return $.ajax(settings)
	                .done(function (response) {
	                    if (response.error) {
	                        uiAlert({
	                            content: response.message
	                        });
	                    }
	                })
	                .fail(function () {
	                    uiAlert({
	                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
	                    });
	                })
	                .always(function () {
	                    $('body').trigger('processStop');
	                });

        }
    });
});

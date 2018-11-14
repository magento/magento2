/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/form/form',
    'mage/translate'
], function ($, uiAlert, uiConfirm, Form, $t) {
    'use strict';

    return Form.extend({
        defaults: {
            confirmationMessage: '',
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            }
        },

        deleteAddress: function (url) {
            var that = this;

            uiConfirm({
                content: this.confirmationMessage,
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        that._delete(url);
                    }
                }
            });
        },

        /**
         * Perform asynchronous DELETE request to server.
         * @param {String} url - ajax url
         * @returns {Deferred}
         */
        _delete: function (url) {
            var settings = _.extend({}, this.ajaxSettings, {
                    url: url,
                    data: {
                        'form_key': window.FORM_KEY
                    }
                }),
                that = this;

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    } else {
                        that.trigger('deleteAddressAction', that.source.get('data.entity_id'));
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

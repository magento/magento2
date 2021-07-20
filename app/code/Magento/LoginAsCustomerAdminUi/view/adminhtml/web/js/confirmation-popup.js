/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Ui/js/modal/confirm',
    'jquery',
    'ko',
    'mage/translate',
    'mage/template',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html'
], function (Component, confirm, $, ko, $t, template, _, alert, selectTpl) {

    'use strict';

    return Component.extend({
        /**
         * Initialize Component
         */
        initialize: function () {
            var self = this,
                content;

            this._super();

            content = '<div class="message message-warning">' + self.content + '</div>';

            if (self.showStoreViewOptions) {
                content = template(
                    selectTpl,
                    {
                        data: {
                            showStoreViewOptions: self.showStoreViewOptions,
                            storeViewOptions: self.storeViewOptions,
                            label: $t('Store View')
                        }
                    }) + content;
            }

            /**
             * Confirmation popup
             *
             * @param {String} url
             * @returns {Boolean}
             */
            window.lacConfirmationPopup = function (url) {
                confirm({
                    title: self.title,
                    content: content,
                    modalClass: 'confirm lac-confirm',
                    actions: {
                        /**
                         * Confirm action.
                         */
                        confirm: function () {
                            var storeId = $('#lac-confirmation-popup-store-id').val(),
                                formKey = $('input[name="form_key"]').val(),
                                params = {};

                            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                            if (storeId) {
                                params.store_id = storeId;
                            }

                            if (formKey) {
                                params.form_key = formKey;
                            }
                            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'json',
                                data: params,
                                showLoader: true,

                                /**
                                 * Open redirect URL in new window, or show messages if they are present
                                 *
                                 * @param {Object} data
                                 */
                                success: function (data) {
                                    var messages = data.messages || [];

                                    if (data.message) {
                                        messages.push(data.message);
                                    }

                                    if (data.redirectUrl) {
                                        window.open(data.redirectUrl);
                                    } else if (messages.length) {
                                        messages = messages.map(function (message) {
                                            return _.escape(message);
                                        });

                                        alert({
                                            content: messages.join('<br>')
                                        });
                                    }
                                },

                                /**
                                 * Show XHR response text
                                 *
                                 * @param {Object} jqXHR
                                 */
                                error: function (jqXHR) {
                                    alert({
                                        content: _.escape(jqXHR.responseText)
                                    });
                                }
                            });
                        }
                    },
                    buttons: [{
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',

                        /**
                         * Click handler.
                         */
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $t('Login as Customer'),
                        class: 'action-primary action-accept',

                        /**
                         * Click handler.
                         */
                        click: function (event) {
                            this.closeModal(event, true);
                        }
                    }]
                });

                return false;
            };
        }
    });
});

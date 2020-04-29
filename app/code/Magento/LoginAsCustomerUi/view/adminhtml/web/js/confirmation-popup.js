/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

define(
    [
        'uiComponent',
        'Magento_Ui/js/modal/confirm',
        'jquery',
        'ko',
        'mage/translate',
        'mage/template',
        'text!Magento_LoginAsCustomerUi/template/confirmation-popup/store-view-ptions.html'
    ],
    function (Component, confirm, $, ko, $t, template, selectTpl, undefined) {

        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;

                var content = '<div class="message message-warning">' + self.content + '</div>';
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
                                var storeId = $('#lac-confirmation-popup-store-id').val();
                                if (storeId) {
                                    url += ((url.indexOf('?') == -1) ? '?' : '&') + 'store_id=' + storeId;
                                }
                                window.open(url);
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
    }
);

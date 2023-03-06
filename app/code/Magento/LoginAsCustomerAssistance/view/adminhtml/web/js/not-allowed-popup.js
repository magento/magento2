/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function (Component, confirm, $t) {

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

            /**
             * Not Allowed popup
             *
             * @returns {Boolean}
             */
            window.lacNotAllowedPopup = function () {
                confirm({
                    title: self.title,
                    content: content,
                    modalClass: 'confirm lac-confirm',
                    buttons: [
                        {
                            text: $t('Close'),
                            class: 'action-secondary action-dismiss',

                            /**
                             * Click handler.
                             */
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }
                    ]
                });

                return false;
            };
        }
    });
});

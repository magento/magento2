/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, Modal, alert, $t) {
    'use strict';

    return Modal.extend({
        defaults: {
            imports: {
                logAction:  '${ $.provider }:data.logAction'
            }
        },

        /**
         * Error handler.
         *
         * @param {Object} xhr - request result.
         */
        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            alert({
                content: xhr.message || $t('An error occurred while logging process.')
            });
        },

        /**
         * Log release notes show
         */
        logReleaseNotesShow: function () {
            var self = this,
                data = {
                    'form_key': window.FORM_KEY
                };

            $.ajax({
                type: 'POST',
                url: this.logAction,
                data: data,
                showLoader: true
            }).done(function (xhr) {
                if (xhr.error) {
                    self.onError(xhr);
                }
            }).fail(this.onError);
        },

        /**
         * Close release notes
         */
        closeReleaseNotes: function () {
            this.logReleaseNotesShow();
            this.closeModal();
        }
    });
});

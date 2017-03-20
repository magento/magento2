/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global  AdminBackup, setLocation */
/* eslint-disable strict */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/mage',
    'prototype'
], function (jQuery) {
    window.AdminBackup = new Class.create();

    AdminBackup.prototype = {
        /**
         * Initialize.
         */
        initialize: function () {
            this.reset();
            this.rollbackUrl = this.backupUrl = '';
        },

        /**
         * reset.
         */
        reset: function () {
            this.time = 0;
            this.type = '';
        },

        /**
         * @param {*} type
         * @return {Boolean}
         */
        backup: function (type) {
            this.reset();
            this.type = type;
            this.requestBackupOptions();

            return false;
        },

        /**
         * @param {*} type
         * @param {*} time
         * @return {Boolean}
         */
        rollback: function (type, time) {
            this.reset();
            this.time = time;
            this.type = type;
            this.showRollbackWarning();

            return false;
        },

        /**
         * Show rollback warning.
         */
        showRollbackWarning: function () {
            this.showPopup('rollback-warning');
        },

        /**
         * request backup options.
         */
        requestBackupOptions: function () {
            var action;

            this.hidePopups();
            action = this.type != 'snapshot' ? 'hide' : 'show'; //eslint-disable-line eqeqeq
            this.showPopup('backup-options');

            $$('#exclude-media-checkbox-container').invoke(action);
        },

        /**
         * Request password.
         */
        requestPassword: function () {
            this.hidePopups();

            this.showPopup('rollback-request-password');

            this.type != 'db' ? //eslint-disable-line eqeqeq
                $('use-ftp-checkbox-row').show() :
                $('use-ftp-checkbox-row').hide();
        },

        /**
         * Toggle Ftp Credentials Form.
         */
        toggleFtpCredentialsForm: function () {
            $('use_ftp').checked ? $('ftp-credentials-container').show()
                : $('ftp-credentials-container').hide();

            $$('#ftp-credentials-container input').each(function (item) {
                if (item.name == 'ftp_path') { //eslint-disable-line eqeqeq
                    return;
                }
                $('use_ftp').checked ? item.addClassName('required-entry') : item.removeClassName('required-entry');
            });
        },

        /**
         * Submit backup.
         */
        submitBackup: function () {
            var data = {
                'type': this.type,
                'maintenance_mode': $('backup_maintenance_mode').checked ? 1 : 0,
                'backup_name': $('backup_name').value,
                'exclude_media': $('exclude_media').checked ? 1 : 0
            };

            new Ajax.Request(this.backupUrl, {
                onSuccess: function (transport) {
                    this.processResponse(transport, 'backup-options');
                }.bind(this),
                method: 'post',
                parameters: data
            });

            this.modal.modal('closeModal');
        },

        /**
         * Submit rollback.
         */
        submitRollback: function () {
            var data = this.getPostData();

            new Ajax.Request(this.rollbackUrl, {
                onSuccess: function (transport) {
                    this.processResponse(transport, 'rollback-request-password');
                }.bind(this),
                method: 'post',
                parameters: data
            });

            this.modal.modal('closeModal');
        },

        /**
         * @param {Object} transport
         * @param {*} popupId
         */
        processResponse: function (transport, popupId) {
            var json;

            if (!transport.responseText.isJSON()) {
                return;
            }

            json = transport.responseText.evalJSON();

            if (json.error) {
                this.showPopup(popupId);
                this.displayError(popupId, json.error);

                return;
            }

            if (json['redirect_url']) {
                setLocation(json['redirect_url']);
            }
        },

        /**
         * @param {*} parentContainer
         * @param {*} message
         */
        displayError: function (parentContainer, message) {
            var messageHtml = this.getErrorMessageHtml(message);

            $$('#' + parentContainer + ' .backup-messages .messages').invoke('update', messageHtml);
            $$('#' + parentContainer + ' .backup-messages').invoke('show');
        },

        /**
         * @param {*} message
         * @return {String}
         */
        getErrorMessageHtml: function (message) {
            return '<div class="message message-error error"><div>' + message + '</div></div>';
        },

        /**
         * @return {*|jQuery}
         */
        getPostData: function () {
            var data = $('rollback-form').serialize(true);

            data.time = this.time;
            data.type = this.type;

            return data;
        },
        backupConfig: {
            'backup-options': {
                title: jQuery.mage.__('Backup options'),

                /**
                 * @return {String}
                 */
                content: function () {
                    return document.getElementById('backup-options-template').textContent;
                },

                /**
                 * Action Ok.
                 */
                actionOk: function () {
                    this.modal.find('#backup-form').validation({
                        submitHandler: jQuery.proxy(this.submitBackup, this)
                    });
                    this.modal.find('#backup-form').submit();
                }
            },
            'rollback-warning': {
                title: jQuery.mage.__('Warning'),

                /**
                 * @return {String}
                 */
                content: function () {
                    return document.getElementById('rollback-warning-template').textContent;
                },

                /**
                 * Action Ok.
                 */
                actionOk: function () {
                    this.modal.modal('closeModal');
                    this.requestPassword();
                }
            },
            'rollback-request-password': {
                title: jQuery.mage.__('Backup options'),

                /**
                 * @return {String}
                 */
                content: function () {
                    return document.getElementById('rollback-request-password-template').textContent;
                },

                /**
                 * Action Ok.
                 */
                actionOk: function () {
                    this.modal.find('#rollback-form').validation({
                        submitHandler: jQuery.proxy(this.submitRollback, this)
                    });
                    this.modal.find('#rollback-form').submit();
                },

                /**
                 * Opened.
                 */
                opened: function () {
                    this.toggleFtpCredentialsForm();
                }
            }
        },

        /**
         * @param {*} divId
         */
        showPopup: function (divId) {
            var self = this;

            this.modal = jQuery('<div/>').attr({
                id: divId
            }).html(this.backupConfig[divId].content()).modal({
                modalClass: 'magento',
                title: this.backupConfig[divId].title,
                type: 'slide',

                /**
                 * @param {juery.Event} e
                 * @param {Object} modal
                 */
                closed: function (e, modal) {
                    modal.modal.remove();
                },

                /**
                 * Opened.
                 */
                opened: function () {
                    if (self.backupConfig[divId].opened) {
                        self.backupConfig[divId].opened.call(self);
                    }
                },
                buttons: [{
                    text: jQuery.mage.__('Cancel'),
                    'class': 'action cancel',

                    /**
                     * Click action.
                     */
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: jQuery.mage.__('Ok'),
                    'class': 'action primary',

                    /**
                     * Click action.
                     */
                    click: function () {
                        self.backupConfig[divId].actionOk.call(self);
                    }
                }]
            });
            this.modal.modal('openModal');
        },

        /**
         * Hide Popups.
         */
        hidePopups: function () {
            var mask;

            $$('.backup-dialog').each(Element.hide);
            mask = $('popup-window-mask');

            if (mask) {
                mask.hide();
            }
        }
    };
});

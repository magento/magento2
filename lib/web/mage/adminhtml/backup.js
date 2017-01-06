/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/mage",
    "prototype"
], function(jQuery){

    AdminBackup = new Class.create();
    
    AdminBackup.prototype = {
        initialize : function(a, b){
            this.reset();
            this.rollbackUrl = this.backupUrl = '';
        },

        reset: function() {
            this.time = 0;
            this.type = '';
        },

        backup: function(type) {
            this.reset();
            this.type = type;
            this.requestBackupOptions();
            return false;
        },

        rollback: function(type, time) {
            this.reset();
            this.time = time;
            this.type = type;
            this.showRollbackWarning();
            return false;
        },

        showRollbackWarning: function() {
            this.showPopup('rollback-warning');
        },

        requestBackupOptions: function() {
            this.hidePopups();
            
            var action = this.type != 'snapshot' ? 'hide' : 'show';
            
            this.showPopup('backup-options');

            $$('#exclude-media-checkbox-container').invoke(action);
        },

        requestPassword: function() {
            this.hidePopups();

            this.showPopup('rollback-request-password');

            this.type != 'db' ?
                $('use-ftp-checkbox-row').show() :
                $('use-ftp-checkbox-row').hide();
        },

        toggleFtpCredentialsForm: function() {
            $('use_ftp').checked ? $('ftp-credentials-container').show()
                : $('ftp-credentials-container').hide();

            $$('#ftp-credentials-container input').each(function(item) {
                if (item.name == 'ftp_path') return;
                $('use_ftp').checked ? item.addClassName('required-entry') : item.removeClassName('required-entry');
            });
        },

        submitBackup: function () {
            var data = {
                'type': this.type,
                'maintenance_mode': $('backup_maintenance_mode').checked ? 1 : 0,
                'backup_name': $('backup_name').value,
                'exclude_media': $('exclude_media').checked ? 1 : 0
            };

            new Ajax.Request(this.backupUrl, {
                onSuccess: function(transport) {
                    this.processResponse(transport, 'backup-options');
                }.bind(this),
                method: 'post',
                parameters: data
            });

            this.modal.modal('closeModal');
        },

        submitRollback: function() {
            var data = this.getPostData();

            new Ajax.Request(this.rollbackUrl, {
                onSuccess: function(transport) {
                    this.processResponse(transport, 'rollback-request-password');
                }.bind(this),
                method: 'post',
                parameters: data
            });

            this.modal.modal('closeModal');
        },

        processResponse: function(transport, popupId) {
            if (!transport.responseText.isJSON()) {
                return;
            }

            var json = transport.responseText.evalJSON();

            if (!!json.error) {
                this.showPopup(popupId);
                this.displayError(popupId, json.error);
                return;
            }

            if (!!json.redirect_url) {
                setLocation(json.redirect_url);
            }
        },

        displayError: function(parentContainer, message) {
            var messageHtml = this.getErrorMessageHtml(message);
            $$('#' + parentContainer + ' .backup-messages .messages').invoke('update', messageHtml);
            $$('#' + parentContainer + ' .backup-messages').invoke('show');
        },

        getErrorMessageHtml: function(message) {
            return '<div class="message message-error error"><div>' + message + '</div></div>';
        },

        getPostData: function() {
            var data = $('rollback-form').serialize(true);
            data['time'] = this.time;
            data['type'] = this.type;
            return data;
        },
        backupConfig: {
            'backup-options': {
                title: jQuery.mage.__('Backup options'),
                content: function () {
                    return document.getElementById('backup-options-template').textContent;
                },
                actionOk: function () {
                    this.modal.find('#backup-form').validation({
                        submitHandler: jQuery.proxy(this.submitBackup, this)
                    });
                    this.modal.find('#backup-form').submit();
                }
            },
            'rollback-warning': {
                title: jQuery.mage.__('Warning'),
                content: function () {
                    return document.getElementById('rollback-warning-template').textContent;
                },
                actionOk: function () {
                    this.modal.modal('closeModal');
                    this.requestPassword();
                }
            },
            'rollback-request-password': {
                title: jQuery.mage.__('Backup options'),
                content: function () {
                    return document.getElementById('rollback-request-password-template').textContent;
                },
                actionOk: function () {
                    this.modal.find('#rollback-form').validation({
                        submitHandler: jQuery.proxy(this.submitRollback, this)
                    });
                    this.modal.find('#rollback-form').submit();
                },
                opened: function () {
                    this.toggleFtpCredentialsForm();
                }
            }
        },
        showPopup: function(divId) {
            var self = this;

            this.modal = jQuery('<div/>').attr({id: divId}).html(this.backupConfig[divId].content()).modal({
                modalClass: 'magento',
                title: this.backupConfig[divId].title,
                type: 'slide',
                closed: function(e, modal){
                    modal.modal.remove();
                },
                opened: function () {
                    if (self.backupConfig[divId].opened) {
                        self.backupConfig[divId].opened.call(self);
                    }
                },
                buttons: [{
                    text: jQuery.mage.__('Cancel'),
                    'class': 'action cancel',
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: jQuery.mage.__('Ok'),
                    'class': 'action primary',
                    click: function () {
                        self.backupConfig[divId].actionOk.call(self);
                    }
                }]
            });
            this.modal.modal('openModal');
        },

        hidePopups: function() {
            $$('.backup-dialog').each(Element.hide);
            var mask = $('popup-window-mask');
            if (mask) {
                mask.hide();
            }
        }
    }

});

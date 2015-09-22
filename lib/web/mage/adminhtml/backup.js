/**
 * Copyright © 2015 Magento. All rights reserved.
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
            this.rollbackForm = jQuery('#rollback-form').mage('validation', {
                submitHandler: jQuery.proxy(this.submitRollback, this)
            });
            this.backupForm = jQuery('#backup-form').mage('validation', {
                submitHandler: jQuery.proxy(this.submitBackup, this)
            });
        },

        reset: function() {
            this.time = 0;
            this.type = '';
            $('use-ftp-checkbox-row').hide();
            $('use_ftp').checked = false;
            $('ftp-credentials-container').hide();
            $$('#ftp-credentials-container input').each(function(item) {
                item.removeClassName('required-entry');
            });
            $('backup_maintenance_mode').checked = false;
            $('rollback_maintenance_mode').checked = false;
            $('exclude_media').checked = false;
            $('password').value = '';
            $('backup_name').value = '';
            $$('.validation-advice').invoke('remove');
            $$('input').invoke('removeClassName', 'validation-failed');
            $$('input').invoke('removeClassName', 'validation-passed');
            $$('.backup-messages').invoke('hide');
            $$('#ftp-credentials-container input').each(function(item) {
                item.value = '';
            });
        },

        backup: function(type) {
            this.reset();
            this.type = type;
            this.showBackupWarning();
            return false;
        },

        rollback: function(type, time) {
            this.reset();
            this.time = time;
            this.type = type;
            this.showRollbackWarning();
            return false;
        },

        showBackupWarning: function() {
            this.showPopup('backup-warning');
        },

        showRollbackWarning: function() {
            this.showPopup('rollback-warning');
        },

        requestBackupOptions: function() {
            this.hidePopups();
            var action = this.type != 'snapshot' ? 'hide' : 'show';
            $$('#exclude-media-checkbox-container').invoke(action);
            this.showPopup('backup-options');
        },

        requestPassword: function() {
            this.hidePopups();
            this.type != 'db' ? $('use-ftp-checkbox-row').show() : $('use-ftp-checkbox-row').hide();
            this.showPopup('rollback-request-password');
        },

        toggleFtpCredentialsForm: function() {
            $('use_ftp').checked ? $('ftp-credentials-container').show()
                : $('ftp-credentials-container').hide();
            var divId = 'rollback-request-password';

            $$('#ftp-credentials-container input').each(function(item) {
                if (item.name == 'ftp_path') return;
                $('use_ftp').checked ? item.addClassName('required-entry') : item.removeClassName('required-entry');
            });

            $(divId).show().setStyle({
                'marginTop': -$(divId).getDimensions().height / 2 + 'px'
            });
        },

        submitBackup: function () {
            var data = {
                'type': this.type,
                'maintenance_mode': $('backup_maintenance_mode').checked ? 1 : 0,
                'backup_name': $('backup_name').value,
                'exclude_media': $('exclude_media').checked ? 1 : 0
            };

            this.modal.modal('closeModal');

            new Ajax.Request(this.backupUrl, {
                onSuccess: function(transport) {
                    this.processResponse(transport, 'backup-options');
                }.bind(this),
                method: 'post',
                parameters: data
            });
        },

        submitRollback: function() {
            var data = this.getPostData();
            this.hidePopups();
            new Ajax.Request(this.rollbackUrl, {
                onSuccess: function(transport) {
                    this.processResponse(transport, 'rollback-request-password');
                }.bind(this),
                method: 'post',
                parameters: data
            });
        },

        processResponse: function(transport, popupId) {
            if (!transport.responseText.isJSON()) {
                return;
            }

            var json = transport.responseText.evalJSON();

            if (!!json.error) {
                this.displayError(popupId, json.error);
                this.showPopup(popupId);
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
            'backup-warning': {
                title: jQuery.mage.__('Warning'),
                content: function () {
                    return jQuery('#backup-warning .message.message-warning').html();
                },
                actionOk: function () {
                    this.modal.modal('closeModal');
                    this.requestBackupOptions();
                }
            },
            'rollback-warning': {
                title: jQuery.mage.__('Warning'),
                content: function () {
                    return jQuery('#rollback-warning .message.message-warning').html();
                },
                actionOk: function () {
                    this.modal.modal('closeModal');
                    this.requestPassword();
                }
            },
            'backup-options': {
                title: jQuery.mage.__('Backup options'),
                content: function () {
                    return jQuery('#backup-options .popup-content').html();
                },
                actionOk: function () {
                    this.modal.find('#backup-form').validation({
                        submitHandler: jQuery.proxy(this.submitBackup, this)
                    });
                    this.modal.find('#backup-form').submit();
                },
                opened: function () {
                    this.modal.find('*:hidden').show();
                }
            },
            'rollback-request-password': {
                title: jQuery.mage.__('Backup options'),
                content: function () {
                    return jQuery('#rollback-request-password .popup-content').html();
                },
                actionOk: function () {
                    this.modal.find('#rollback-request-password').validation({
                        submitHandler: jQuery.proxy(this.submitRollback, this)
                    });
                    this.modal.find('#rollback-request-password').submit();
                },
                opened: function () {
                    this.modal.find('*:hidden').show();
                }
            }
        },
        showPopup: function(divId) {
            var self = this;

            this.modal = jQuery('<div/>').html(this.backupConfig[divId].content()).modal({
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
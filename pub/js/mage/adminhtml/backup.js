/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var AdminBackup = new Class.create();
AdminBackup.prototype = {
    initialize : function(a, b){
        this.reset();
        this.rollbackUrl = this.backupUrl = '';
        this.rollbackValidator = new Validation($('rollback-form'));
        this.backupValidator = new Validation($('backup-form'));
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
        if (!!this.backupValidator && this.backupValidator.validate()) {
            this.hidePopups();
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
        }
        return false;
    },

    submitRollback: function() {
        if (!!this.rollbackValidator && this.rollbackValidator.validate()) {
            var data = this.getPostData();
            this.hidePopups();
            new Ajax.Request(this.rollbackUrl, {
                onSuccess: function(transport) {
                    this.processResponse(transport, 'rollback-request-password');
                }.bind(this),
                method: 'post',
                parameters: data
            });
        }
        return false;
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
        return '<li class="error-msg"><ul><li><span>' + message + '</span></li></ul></li>';
    },

    getPostData: function() {
        var data = $('rollback-form').serialize(true);
        data['time'] = this.time;
        data['type'] = this.type;
        return data;
    },

    showPopup: function(divId) {
        $(divId).show().setStyle({
            'marginTop': -$(divId).getDimensions().height / 2 + 'px'
        });
        $('popup-window-mask').setStyle({
            height: $('html-body').getHeight() + 'px'
        }).show();
    },

    hidePopups: function() {
        $$('.backup-dialog').each(Element.hide);
        $('popup-window-mask').hide();
    }
}

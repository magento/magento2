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
 * @category    mage.js
 * @package     test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
TranslateInlineTest = TestCase('TranslateInlineTest');
TranslateInlineTest.prototype.testInit = function() {
    var translateInline = jQuery(document).translateInline();
    assertEquals(true, translateInline.is(':mage-translateInline'));
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testCreate = function() {
    var options = {
            translateForm: {
                data:{
                    id: 'translate-form-id'
                }
            },
            dialog:{
                id: 'dialog-id'
            },
            editTrigger: {
                template: '<img id="edit-trigger-id" alt="${alt}" src="${img}">'
            }
        },
        translateInline = jQuery(document).translateInline(options);
    assertEquals(true, jQuery('#' + options.dialog.id).size() > 0);
    assertEquals(true, jQuery('#edit-trigger-id').size() > 0);
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testDialogOpenOnEdit = function() {
    var options= {
            dialog: {
                id: 'dialog-id'
            }
        };
    var translateInline = jQuery(document).translateInline(options),
        dialog = jQuery('#' + options.dialog.id),
        dialogHiddenOnTranslateInlineCreate = dialog.is(':hidden');
    translateInline.trigger('edit.editTrigger');
    var dialogVisibleAfterTriggerEdit = dialog.is(':visible');
    assertEquals(true, dialogHiddenOnTranslateInlineCreate);
    assertEquals(true, dialogVisibleAfterTriggerEdit);
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testTranslationFormTemplate = function() {
    var options = {
            translateForm: {
                template:'<div id="${data.id}">${data.newTemplateVariable}</div>',
                data:{
                    id: 'translate-form-id',
                    newTemplateVariable: 'New Template Variable'
                }
            }
        },
        translateInline = jQuery(document).translateInline(options);
    translateInline.trigger('edit.editTrigger');
    var translateForm = jQuery('#' + options.translateForm.data.id);
    assertEquals(true, translateForm.size() > 0);
    assertEquals(true, translateForm.text() === options.translateForm.data.newTemplateVariable);
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testTranslateFormSubmit = function() {
    FORM_KEY = 'form_key';
    var options = {
            ajaxUrl: 'www.test.com',
            area: 'test',
            translateForm: {
                template:'<form id="${data.id}"><input name="test" value="test" /></form>',
                data:{
                    id: 'translate-form-id'
                }
            },
            dialog: {
                id: 'dialog-id',
                buttons : [{
                    'class': 'submit-button'
                }]
            }
        },
        translateInline = jQuery(document).translateInline(options),
        submit = jQuery('.ui-dialog-buttonset .submit-button'),
        ajaxParametersCorrect = false;

    translateInline.trigger('edit.editTrigger');
    var parameters = jQuery.param({area: options.area}) +
        '&' + jQuery('#' + options.translateForm.data.id).serialize(),
        dialog = jQuery('#' + options.dialog.id),
        dialogVisibleOnAjaxSend = false,
        dialogHiddenAfterAjaxComplete = false;
    jQuery(document)
        .on('ajaxSend', function(e, jqXHR, settings){
            jqXHR.abort();
            dialogVisibleOnAjaxSend = dialog.is(':visible');
            ajaxParametersCorrect = settings.data.indexOf(parameters) >= 0;
            jQuery(this).trigger('ajaxComplete');
        });
    submit.trigger('click');
    assertEquals(true, dialogVisibleOnAjaxSend);
    assertEquals(true, ajaxParametersCorrect);
    assertEquals(true, dialog.is(':hidden'));
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testDestroy = function() {
    var options = {
            translateForm: {
                data:{
                    id: 'translate-form-id'
                }
            },
            dialog:{
                id: 'dialog-id'
            },
            editTrigger: {
                template: '<img id="edit-trigger-id" alt="${alt}" src="${img}">'
            }
        },
        translateInline = jQuery(document).translateInline(options),
        dialog = jQuery('#' + options.dialog.id),
        editTrigger = jQuery('#edit-trigger-id'),
        dialogCreated = dialog.size() && dialog.is(':ui-dialog'),
        editTriggerCreated = editTrigger.size() && jQuery(document).is(':mage-editTrigger'),
        editTriggerEventIsBound = false;

    assertEquals(true, dialogCreated);
    assertEquals(true, editTriggerCreated);
    translateInline.on('edit.editTrigger', function(){editTriggerEventIsBound = true;});
    translateInline.translateInline('destroy');
    translateInline.trigger('edit.editTrigger');
    assertEquals(false, dialog.size() && dialog.is(':ui-dialog'));
    assertEquals(false, editTrigger.size() && jQuery(document).is(':mage-editTrigger'));
    assertEquals(false, editTriggerEventIsBound);
};
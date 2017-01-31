/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
TranslateInlineTest = TestCase('TranslateInlineTest');
TranslateInlineTest.prototype.testInit = function() {
    /*:DOC += <script id="translate-form-template" type="text/x-magento-template">
      </script>
      <div data-role="translate-dialog"></div>
     */
    var translateInline = jQuery('[data-role="translate-dialog"]').translateInline();
    assertTrue(translateInline.is(':mage-translateInline'));
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testDialogOpenOnEdit = function() {
    /*:DOC += <script id="translate-form-template" type="text/x-magento-template">
      </script>
      <div data-role="translate-dialog"></div>
     */
    var options= {
            dialog: {
                id: 'dialog-id'
            }
        };
    var translateInline = jQuery('[data-role="translate-dialog"]').translateInline(options),
        isDialogHiddenOnInit = translateInline.is(':hidden');
    translateInline.trigger('edit.editTrigger');
    var dialogVisibleAfterTriggerEdit = translateInline.is(':visible');
    assertTrue(isDialogHiddenOnInit);
    assertTrue(dialogVisibleAfterTriggerEdit);
    translateInline.translateInline('destroy');
};
TranslateInlineTest.prototype.testTranslationFormTemplate = function() {
    /*:DOC += <script id="translate-form-template" type="text/x-magento-template">
      <form id="<%= data.id %>"><%= data.newTemplateVariable %></form>
      </script>
      <div data-role="translate-dialog"></div>
     */
    var options = {
            translateForm: {
                data:{
                    id: 'translate-form-id',
                    newTemplateVariable: 'New Template Variable'
                }
            }
        },
        translateInline = jQuery('[data-role="translate-dialog"]').translateInline(options);
    translateInline.trigger('edit.editTrigger');
    var translateForm = jQuery('#' + options.translateForm.data.id);
    assertTrue(translateForm.size() > 0);
    assertEquals(translateForm.text(), options.translateForm.data.newTemplateVariable);
    translateInline.translateInline('destroy');
};
// @TODO Need to be fixed to avoid errors on the bamboo server in context of MAGETWO-5085 ticket
/*TranslateInlineTest.prototype._testTranslateFormSubmit = function() {
    FORM_KEY = 'form_key';
    var options = {
            ajaxUrl: 'www.test.com',
            area: 'test',
            translateForm: {
                template:'<form id="<%= data.id %>"><input name="test" value="test" /></form>',
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
};*/
TranslateInlineTest.prototype.testDestroy = function() {
    /*:DOC += <script id="translate-form-template" type="text/x-magento-template">
      <form id="<%= data.id %>"><%= data.newTemplateVariable %></form>
      </script>
      <div data-role="translate-dialog"></div>
      <img id="edit-trigger-id">
     */
    var options = {
            translateForm: {
                data:{
                    id: 'translate-form-id',
                    newTemplateVariable: ''
                }
            }
        },
        translateInline = jQuery('[data-role="translate-dialog"]').translateInline(options),
        editTrigger = jQuery('#edit-trigger-id').editTrigger(),
        editTriggerCreated = editTrigger.size() && jQuery('#edit-trigger-id').is(':mage-editTrigger'),
        editTriggerEventIsBound = false;

    assertTrue(translateInline.is(':mage-translateInline'));
    assertTrue(editTriggerCreated);
    translateInline.on('edit.editTrigger', function(){editTriggerEventIsBound = true;});
    translateInline.translateInline('destroy');
    translateInline.trigger('edit.editTrigger');
    assertFalse(translateInline.is(':mage-translateInline'));
    assertFalse(editTriggerEventIsBound);
};

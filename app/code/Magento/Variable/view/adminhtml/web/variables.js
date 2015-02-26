/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "prototype"
], function(jQuery){

window.Variables = {
    textareaElementId: null,
    variablesContent: null,
    dialogWindow: null,
    dialogWindowId: 'variables-chooser',
    overlayShowEffectOptions: null,
    overlayHideEffectOptions: null,
    insertFunction: 'Variables.insertVariable',
    init: function(textareaElementId, insertFunction) {
        if ($(textareaElementId)) {
            this.textareaElementId = textareaElementId;
        }
        if (insertFunction) {
            this.insertFunction = insertFunction;
        }
    },

    resetData: function() {
        this.variablesContent = null;
        this.dialogWindow = null;
    },

    openVariableChooser: function(variables) {
        if (this.variablesContent == null && variables) {
            this.variablesContent = '<ul class="insert-variable">';
            variables.each(function(variableGroup) {
                if (variableGroup.label && variableGroup.value) {
                    this.variablesContent += '<li><b>' + variableGroup.label + '</b></li>';
                    (variableGroup.value).each(function(variable){
                        if (variable.value && variable.label) {
                            this.variablesContent += '<li>' +
                                this.prepareVariableRow(variable.value, variable.label) + '</li>';
                        }
                    }.bind(this));
                }
            }.bind(this));
            this.variablesContent += '</ul>';
        }
        if (this.variablesContent) {
            this.openDialogWindow(this.variablesContent);
        }
    },
    openDialogWindow: function(variablesContent) {
        var windowId = this.dialogWindowId;
        jQuery('body').append('<div id="' + windowId + '">'+ Variables.variablesContent +'</div>');
        jQuery('#' + windowId).dialog({
            autoOpen:   false,
            title:      "Insert Variable...",
            modal:      true,
            resizable:  false,
            minWidth:   500,
            close:      function(event, ui) {
                jQuery(this).dialog('destroy');
                jQuery('#' + windowId).remove();
            }
        });

        jQuery('#' + windowId).dialog('open');

        variablesContent.evalScripts.bind(variablesContent).defer();
    },
    closeDialogWindow: function(window) {
        var windowId = this.dialogWindowId;
        if(jQuery('#' + windowId).length){
            jQuery('#' + windowId).dialog('close');
        }
    },
    prepareVariableRow: function(varValue, varLabel) {
        var value = (varValue).replace(/"/g, '&quot;').replace(/'/g, '\\&#39;');
        var content = '<a href="#" onclick="'+this.insertFunction+'(\''+ value +'\');return false;">' + varLabel + '</a>';
        return content;
    },
    insertVariable: function(value) {
        var windowId = this.dialogWindowId;
        jQuery('#' + windowId).dialog('close');
        var textareaElm = $(this.textareaElementId);
        if (textareaElm) {
            var scrollPos = textareaElm.scrollTop;
            updateElementAtCursor(textareaElm, value);
            textareaElm.focus();
            textareaElm.scrollTop = scrollPos;
            textareaElm = null;
        }
        return;
    }
};

window.MagentovariablePlugin = {
    editor: null,
    variables: null,
    textareaId: null,
    setEditor: function(editor) {
        this.editor = editor;
    },
    loadChooser: function(url, textareaId) {
        this.textareaId = textareaId;
        if (this.variables == null) {
            new Ajax.Request(url, {
                parameters: {},
                onComplete: function (transport) {
                    if (transport.responseText.isJSON()) {
                        Variables.init(null, 'MagentovariablePlugin.insertVariable');
                        this.variables = transport.responseText.evalJSON();
                        this.openChooser(this.variables);
                    }
                }.bind(this)
             });
        } else {
            this.openChooser(this.variables);
        }
        return;
    },
    openChooser: function(variables) {
        Variables.openVariableChooser(variables);
    },
    insertVariable : function (value) {
        if (this.textareaId) {
            Variables.init(this.textareaId);
            Variables.insertVariable(value);
        } else {
            Variables.closeDialogWindow();
            this.editor.execCommand('mceInsertContent', false, value);
        }
        return;
    }
};

});
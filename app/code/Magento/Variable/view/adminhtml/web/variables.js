/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
    'prototype',
], function(jQuery, $t){

window.Variables = {
    textareaElementId: null,
    variablesContent: null,
    dialogWindow: null,
    dialogWindowId: 'variables-chooser',
    overlayShowEffectOptions: null,
    overlayHideEffectOptions: null,
    insertFunction: 'Variables.insertVariable',
    variablesValue: [],

    /**
     * @param {*} textareaElementId
     * @param {Function} insertFunction
     */
    init: function(textareaElementId, insertFunction) {
        if ($(textareaElementId)) {
            this.textareaElementId = textareaElementId;
        }
        if (insertFunction) {
            this.insertFunction = insertFunction;
        }
    },

    /**
     * reset data.
     */
    resetData: function() {
        this.variablesContent = null;
        this.dialogWindow = null;
    },

    /**
     * @param {Object} variables
     */
    openVariableChooser: function(variables) {
        if (this.variablesContent == null && variables) {
            this.variablesContent = '<ul class="insert-variable">';
            variables.each(function(variableGroup) {
                if (variableGroup.label && variableGroup.value) {
                    this.variablesContent += '<li><b>' + variableGroup.label.escapeHTML() + '</b></li>';
                    (variableGroup.value).each(function(variable){
                        if (variable.value && variable.label) {
                            this.variablesValue.push(variable.value);
                            this.variablesContent += '<li>' +
                                this.prepareVariableRow(this.variablesValue.length, variable.label) + '</li>';
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

    /**
     * @param {*} variablesContent
     */
    openDialogWindow: function (variablesContent) {
        var windowId = this.dialogWindowId;
        jQuery('<div id="' + windowId + '">' + variablesContent + '</div>').modal({
            title: $t('Insert Variable...'),
            type: 'slide',
            buttons: [],
            closed: function (e, modal) {
                modal.modal.remove();
            }
        });

        jQuery('#' + windowId).modal('openModal');
    },

    /**
     * Close dialog window.
     */
    closeDialogWindow: function() {
        jQuery('#' + this.dialogWindowId).modal('closeModal');
    },

    /**
     * @param {Number} index
     * @param {*} varLabel
     * @return {String}
     */
    prepareVariableRow: function (index, varLabel) {
        return '<a href="#" onclick="' +
            this.insertFunction +
            '(' +
            index +
            ');return false;">' +
            varLabel.escapeHTML() +
            '</a>';
    },

    /**
     * @param {*} variable
     */
    insertVariable: function(variable) {
        var windowId = this.dialogWindowId;
        jQuery('#' + windowId).modal('closeModal');
        var textareaElm = $(this.textareaElementId);
        
        if (textareaElm) {
            var scrollPos = textareaElm.scrollTop;

            if (!isNaN(variable)) {
                updateElementAtCursor(textareaElm, Variables.variablesValue[variable - 1]);
            } else {
                updateElementAtCursor(textareaElm, variable);
            }
            textareaElm.focus();
            textareaElm.scrollTop = scrollPos;
            jQuery(textareaElm).change();
            textareaElm = null;
        }
    }
};

window.MagentovariablePlugin = {
    editor: null,
    variables: null,
    textareaId: null,

    /**
     * @param {*} editor
     */
    setEditor: function(editor) {
        this.editor = editor;
    },

    /**
     * @param {String} url
     * @param {*} textareaId
     */
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
    },

    /**
     * @param {*} variables
     */
    openChooser: function(variables) {
        Variables.openVariableChooser(variables);
    },

    /**
     * @param {*} value
     */
    insertVariable : function (value) {
        if (this.textareaId) {
            Variables.init(this.textareaId);
            Variables.insertVariable(value);
        } else {
            Variables.closeDialogWindow();
            this.editor.execCommand('mceInsertContent', false, value);
        }
    }
};

});

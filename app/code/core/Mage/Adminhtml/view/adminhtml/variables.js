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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var Variables = {
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
            this.variablesContent = '<ul>';
            variables.each(function(variableGroup) {
                if (variableGroup.label && variableGroup.value) {
                    this.variablesContent += '<li><b>' + variableGroup.label + '</b></li>';
                    (variableGroup.value).each(function(variable){
                        if (variable.value && variable.label) {
                            this.variablesContent += '<li style="padding-left: 20px;">' +
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
        if ($(this.dialogWindowId) && typeof(Windows) != 'undefined') {
            Windows.focus(this.dialogWindowId);
            return;
        }

        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
        Windows.overlayShowEffectOptions = {duration:0};
        Windows.overlayHideEffectOptions = {duration:0};

        this.dialogWindow = Dialog.info(variablesContent, {
            draggable:true,
            resizable:true,
            closable:true,
            className:"magento",
            windowClassName:"popup-window",
            title:'Insert Variable...',
            width:700,
            //height:270,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:this.dialogWindowId,
            onClose: this.closeDialogWindow.bind(this)
        });
        variablesContent.evalScripts.bind(variablesContent).defer();
    },
    closeDialogWindow: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            window.close();
            Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
        }
    },
    prepareVariableRow: function(varValue, varLabel) {
        var value = (varValue).replace(/"/g, '&quot;').replace(/'/g, '\\&#39;');
        var content = '<a href="#" onclick="'+this.insertFunction+'(\''+ value +'\');">' + varLabel + '</a>';
        return content;
    },
    insertVariable: function(value) {
        this.closeDialogWindow(this.dialogWindow);
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

MagentovariablePlugin = {
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

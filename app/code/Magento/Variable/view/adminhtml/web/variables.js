/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Variables, updateElementAtCursor */
define([
    'jquery',
    'mage/translate',
    'wysiwygAdapter',
    'uiRegistry',
    'ko',
    'mage/apply/main',
    'mageUtils',
    'Magento_Variable/js/config-directive-generator',
    'Magento_Variable/js/custom-directive-generator',
    'Magento_Ui/js/lib/spinner',
    'jquery/ui',
    'prototype'
], function (jQuery, $t, wysiwyg, registry, ko, mageApply, utils, configGenerator, customGenerator, loader) {
    'use strict';

    window.Variables = {
        textareaElementId: null,
        variablesContent: null,
        dialogWindow: null,
        dialogWindowId: 'variables-chooser',
        overlayShowEffectOptions: null,
        overlayHideEffectOptions: null,
        insertFunction: 'Variables.insertVariable',
        selectedPlaceholder: null,
        isEditMode: ko.observable(),

        /**
         * @param {*} textareaElementId
         * @param {Function} insertFunction
         * @param {Object} selectedPlaceholder
         */
        init: function (textareaElementId, insertFunction, selectedPlaceholder) {
            if ($(textareaElementId)) {
                this.textareaElementId = textareaElementId;
            }

            if (insertFunction) {
                this.insertFunction = insertFunction;
            }

            if (selectedPlaceholder) {
                this.selectedPlaceholder = selectedPlaceholder;
            }

        },

        /**
         * reset data.
         */
        resetData: function () {
            this.variablesContent = null;
            this.dialogWindow = null;
        },

        /**
         * @param {Object} variables
         */
        openVariableChooser: function (variables) {
            if (variables) {
                this.openDialogWindow(variables);
            }
        },

        /**
         * Close dialog window.
         */
        closeDialogWindow: function () {
            jQuery('#' + this.dialogWindowId).modal('closeModal');
        },

        /**
         * Init ui component grid on the form
         *
         * @return void
         */
        initUiGrid: function () {
            mageApply.apply(document.getElementById(this.dialogWindow));
            jQuery('#' + this.dialogWindowId).applyBindings();
            loader.get('variables_modal.variables_modal.variables').hide();
        },


        /**
         * @param {*} variablesContent
         */
        openDialogWindow: function (variablesContent, variableCode, selectedElement) {
            var html = utils.copy(variablesContent),
            self = this;

            jQuery('<div id="' + this.dialogWindowId + '">' + html + '</div>').modal({
                title: self.isEditMode() ? $t('Edit variable...') : $t('Insert Variable...') ,
                type: 'slide',
                buttons: self.getButtonsConfig(self.isEditMode()),

                closed: function (e, modal) {
                    modal.modal.remove();

                    //remove selected value from registry
                    registry.get('variables_modal.variables_modal.variables.variable_selector', function (radioSelect) {
                        radioSelect.selectedVariableCode('');
                    });
                }
            });
            this.selectedPlaceholder = selectedElement;

            jQuery('#' + this.dialogWindowId).modal('openModal');

            if (typeof variableCode != 'undefined') {
                //@TODO: workaround should be replaced
                registry.get('variables_modal.variables_modal.variables.variable_selector', function (radioSelect) {
                    radioSelect.selectedVariableCode(variableCode);
                });
            }


        },

        /**
         * Get selected variable code
         *
         * @returns {*}
         */
        getVariableCode: function () {
            var code = registry.get('variables_modal.variables_modal.variables.variable_selector')
                .selectedVariableCode(),
                directive = code;

            // processing switch here as content must contain only path/code without type
            if (typeof code != 'undefined') {
                if (code.match('^default:')) {
                    directive = configGenerator.processConfig(code.replace('default:', ''));
                } else if (code.match('^custom:')) {
                    directive = customGenerator.processConfig(code.replace('custom:', ''));
                }
                return directive
            }
        },


        getButtonsConfig: function (isEditMode) {

            var self = this,  buttonsData;

            buttonsData = [
                {
                    text: $t('Cancel'),
                    'class': 'action-scalable cancel',

                    /**
                     * @param {jQuery.Event} event
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                },
                {
                    text: isEditMode ? $t('Save'): $t('Insert Variable'),
                    'class': 'action-primary',
                    'attr': {'id': 'insert_variable'},

                    /** Insert Variable */
                    click: function () {
                        self.insertVariable(self.getVariableCode());
                    }
                }
                ];
            if (isEditMode) {

                buttonsData.push(
                    {
                        text: $t('Delete'),
                        'class': 'action-scalable confirm',

                        /**
                         * @param {jQuery.Event} event
                         */
                        click: function (event) {
                            self.removeVariable(event);
                        }
                    }
                )
            }

            return buttonsData
        },

        /**
         * @param {String} varValue
         * @param {*} varLabel
         * @return {String}
         */
        prepareVariableRow: function (varValue, varLabel) {
            var value = varValue.replace(/"/g, '&quot;').replace(/'/g, '\\&#39;'),
                content = '<a href="#" onclick="' +
                    this.insertFunction +
                    '(\'' +
                    value +
                    '\');return false;">' +
                    varLabel +
                    '</a>';

            return content;
        },

        /**
         * @param {*} value
         */
        insertVariable: function (value) {
            var windowId = this.dialogWindowId,
                textareaElm, scrollPos;

            jQuery('#' + windowId).modal('closeModal');
            textareaElm = $(this.textareaElementId);

            if (typeof wysiwyg != 'undefined' && wysiwyg.activeEditor()) {
                wysiwyg.activeEditor().focus();
                wysiwyg.activeEditor().execCommand('mceInsertContent', false,
                    value);
                if (this.selectedPlaceholder) {
                    this.selectedPlaceholder.remove();
                }

            } else if (textareaElm) {
                scrollPos = textareaElm.scrollTop;
                updateElementAtCursor(textareaElm, value);
                textareaElm.focus();
                textareaElm.scrollTop = scrollPos;
                jQuery(textareaElm).change();
                textareaElm = null;
            }

            return;
        },

        removeVariable: function () {
            var windowId = this.dialogWindowId;

            jQuery('#' + windowId).modal('closeModal');

            if (typeof wysiwyg != 'undefined' && wysiwyg.activeEditor()) {
                wysiwyg.activeEditor().focus();
                wysiwyg.activeEditor().execCommand('mceRemoveNode', false);
                if (this.selectedPlaceholder) {
                    this.selectedPlaceholder.remove();
                }
            }
            return;
        }
    };

    window.MagentovariablePlugin = {
        editor: null,
        variables: null,
        textareaId: null,

        /**
         * @param {*} editor
         */
        setEditor: function (editor) {
            this.editor = editor;
        },

        /**
         * @param {String} url
         * @param {*} textareaId
         *
         */
        loadChooser: function (url, textareaId, variableCode, selectedElement) {
            this.textareaId = textareaId;
                new Ajax.Request(url, {
                    parameters: {},
                    onComplete: function (transport) {
                        Variables.init(this.textareaId, 'MagentovariablePlugin.insertVariable');
                        Variables.isEditMode(variableCode);
                        this.variablesContent = transport.responseText;
                        Variables.openDialogWindow(this.variablesContent, variableCode, selectedElement);
                        Variables.initUiGrid();
                    }.bind(this)
                });

            return;
        },

        /**
         * @param {*} value
         */
        insertVariable: function (value) {
            if (this.textareaId) {
                Variables.init(this.textareaId);
                Variables.insertVariable(value);
            } else {
                Variables.closeDialogWindow();
                Variables.insertVariable(value);
            }

            return;
        }
    };
});

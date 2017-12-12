/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Variables, updateElementAtCursor, MagentovariablePlugin, Base64 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'wysiwygAdapter',
    'uiRegistry',
    'mage/apply/main',
    'mageUtils',
    'Magento_Variable/js/config-directive-generator',
    'Magento_Variable/js/custom-directive-generator',
    'Magento_Ui/js/lib/spinner',
    'jquery/ui',
    'prototype'
], function (jQuery, alert, $t, wysiwyg, registry, mageApply, utils, configGenerator, customGenerator, loader) {
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
        isEditMode: null,
        editor: null,

        /**
         * Initialize Variables handler.
         *
         * @param {*} textareaElementId
         * @param {Function} insertFunction
         * @param {Object} editor
         * @param {Object} selectedPlaceholder
         */
        init: function (textareaElementId, insertFunction, editor, selectedPlaceholder) {
            if ($(textareaElementId)) {
                this.textareaElementId = textareaElementId;
            }

            if (insertFunction) {
                this.insertFunction = insertFunction;
            }

            if (selectedPlaceholder) {
                this.selectedPlaceholder = selectedPlaceholder;
            }

            if (editor) {
                this.editor = editor;
            }
        },

        /**
         * Reset data.
         */
        resetData: function () {
            this.variablesContent = null;
            this.dialogWindow = null;
        },

        /**
         * Open variables chooser slideout.
         *
         * @param {Object} variables
         */
        openVariableChooser: function (variables) {
            if (variables) {
                this.openDialogWindow(variables);
            }
        },

        /**
         * Close variables chooser slideout dialog window.
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
         * Open slideout dialog window.
         *
         * @param {*} variablesContent
         * @param {Object} selectedElement
         */
        openDialogWindow: function (variablesContent, selectedElement) {

            var html = utils.copy(variablesContent),
                self = this;

            jQuery('<div id="' + this.dialogWindowId + '">' + html + '</div>').modal({
                title: self.isEditMode ? $t('Edit Variable') : $t('Insert Variable'),
                type: 'slide',
                buttons: self.getButtonsConfig(self.isEditMode),

                /**
                 * @param {jQuery.Event} e
                 * @param {Object} modal
                 */
                closed: function (e, modal) {
                    modal.modal.remove();
                    registry.get(
                        'variables_modal.variables_modal.variables.variable_selector',
                        function (radioSelect) {
                            radioSelect.selectedVariableCode('');
                        }
                    );
                }
            });

            this.selectedPlaceholder = selectedElement;

            jQuery('#' + this.dialogWindowId).modal('openModal');

            if (typeof selectedElement !== 'undefined') {
                var variablePath = MagentovariablePlugin.getElementVariablePath(selectedElement);
                registry.get(
                    'variables_modal.variables_modal.variables.variable_selector',
                    function (radioSelect) {
                        radioSelect.selectedVariableCode(MagentovariablePlugin.getElementVariablePath(selectedElement));
                    }
                );
            }
        },

        /**
         * Get selected variable directive.
         *
         * @returns {*}
         */
        getVariableCode: function () {
            var code = registry.get('variables_modal.variables_modal.variables.variable_selector')
                    .selectedVariableCode(),
                directive = code;

            // processing switch here as content must contain only path/code without type
            if (typeof code !== 'undefined') {
                if (code.match('^default:')) {
                    directive = configGenerator.processConfig(code.replace('default:', ''));
                } else if (code.match('^custom:')) {
                    directive = customGenerator.processConfig(code.replace('custom:', ''));
                }

                return directive;
            }
        },

        /**
         * Get buttons configuration for slideout dialog.
         *
         * @param {Boolean} isEditMode
         *
         * @returns {Array}
         */
        getButtonsConfig: function (isEditMode) {

            var self = this,
                buttonsData;

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

                    text: isEditMode ? $t('Save') : $t('Insert Variable'),
                    class: 'action-primary ' + (isEditMode ? '' : 'disabled'),
                    attr: {
                        'id': 'insert_variable'
                    },

                    /**
                     * Insert Variable
                     */
                    click: function () {
                        self.insertVariable(self.getVariableCode());
                    }
                }
            ];

            return buttonsData;
        },

        /**
         * Prepare variables row.
         *
         * @param {String} varValue
         * @param {*} varLabel
         * @return {String}
         * @deprecated This method isn't relevant after ui changes
         */
        prepareVariableRow: function (varValue, varLabel) {
            var value = varValue.replace(/"/g, '&quot;').replace(/'/g, '\\&#39;');

            return '<a href="#" onclick="' +
                this.insertFunction +
                '(\'' +
                value +
                '\');return false;">' +
                varLabel +
                '</a>';
        },

        /**
         * Insert variable into WYSIWYG editor.
         *
         * @param {*} value
         * @return {Object}
         */
        insertVariable: function (value) {
            var windowId = this.dialogWindowId,
                textareaElm, scrollPos, wysiwygEditorFocused;

            jQuery('#' + windowId).modal('closeModal');
            textareaElm = $(this.textareaElementId);

            //to support switching between wysiwyg editors
            if (wysiwyg && wysiwyg.activeEditor()) {
                wysiwygEditorFocused = !!wysiwyg.get(this.textareaElementId);
            }

            if (wysiwygEditorFocused) {
                wysiwyg.setCursorLocation(this.selectedPlaceholder, 1);
                wysiwyg.insertContent(value, false);

                if (this.selectedPlaceholder && jQuery(this.selectedPlaceholder).hasClass('magento-variable')) {
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

            return this;
        }

    };

    window.MagentovariablePlugin = {
        editor: null,
        variables: null,
        textareaId: null,
        lostVariables: [],

        /**
         * Reset lost variables array.
         */
        resetLostVariables: function () {
            this.lostVariables = [];
        },

        /**
         * Register new variable as lost.
         *
         * @param {String} variableCode
         */
        registerLostVariable: function (variableCode) {
            this.lostVariables.push(variableCode);
        },

        /**
         * Show information message on lost variables.
         */
        informLostVariables: function () {
            var msg = $t(
                'This page contains {0} unexistent variable(s): {1}. Please remove them or replace with valid ones.'
            );

            msg = msg.replace(/\{(\d+)\}/g, function (match, index) {
                var params = [this.lostVariables.length, this.lostVariables.join(', ')];
                return (typeof (params[index]) !== 'undefined') ? params[index] : match;
            }.bind(this));

            if (this.lostVariables.length > 0) {
                alert({
                    content: msg
                });
            }
        },

        /**
         * Bind editor.
         *
         * @param {*} editor
         */
        setEditor: function (editor) {
            this.editor = editor;
        },

        /**
         * Load variables chooser.
         *
         * @param {String} url
         * @param {*} textareaId
         * @param {Object} selectedElement
         *
         * @return {Object}
         */
        loadChooser: function (url, textareaId, selectedElement) {
            this.textareaId = textareaId;

            new Ajax.Request(url, {
                parameters: {},
                onComplete: function (transport) {
                    Variables.init(this.textareaId, 'MagentovariablePlugin.insertVariable', this.editor);
                    Variables.isEditMode = !!this.getElementVariablePath(selectedElement);
                    this.variablesContent = transport.responseText;
                    Variables.openDialogWindow(this.variablesContent, selectedElement);
                    Variables.initUiGrid();
                }.bind(this)
            });

            return this;
        },

        /**
         * Open variables chooser window.
         *
         * @param {*} variables
         * @deprecated This method isn't relevant after ui changes
         */
        openChooser: function (variables) {
            Variables.openVariableChooser(variables);
        },

        /**
         * Insert variable.
         *
         * @param {*} value
         *
         * @return {Object}
         */
        insertVariable: function (value) {
            if (this.textareaId) {
                Variables.init(this.textareaId);
                Variables.insertVariable(value);
            } else {
                Variables.closeDialogWindow();
                Variables.insertVariable(value);
            }

            return this;
        },

        /**
         * Get element variable path.
         *
         * @param {Object} element
         * @returns {String}
         */
        getElementVariablePath: function (element) {
            var type, code;

            if (!element || !jQuery(element).hasClass('magento-variable')) {
                return '';
            }
            type = jQuery(element).hasClass('magento-custom-var') ? 'custom' : 'default';
            code = Base64.idDecode(element.getAttribute('id'));

            return type + ':' + code;
        }
    };
});

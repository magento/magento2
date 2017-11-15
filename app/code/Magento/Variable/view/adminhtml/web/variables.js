/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Variables, updateElementAtCursor */
define([
    'jquery',
    'mage/translate',
    'uiRegistry',
    'ko',
    'mage/apply/main',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
    'prototype'
], function (jQuery, $t, registry, ko, mageApply) {
    'use strict';

    window.Variables = {
        textareaElementId: null,
        variablesContent: null,
        dialogWindow: null,
        dialogWindowId: 'variables-chooser',
        overlayShowEffectOptions: null,
        overlayHideEffectOptions: null,
        insertFunction: 'Variables.insertVariable',

        /**
         * @param {*} textareaElementId
         * @param {Function} insertFunction
         */
        init: function (textareaElementId, insertFunction) {
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
         * @param {*} variablesContent
         */
        openDialogWindow: function (variablesContent) {
            var windowId = this.dialogWindowId;

            jQuery('<div id="' + windowId + '">' + variablesContent + '</div>').modal({
                title: $t('Insert Variable...'),
                type: 'slide',
                buttons: [],

                /** @inheritdoc */
                closed: function (e, modal) {
                    modal.modal.remove();
                }
            });

            jQuery('#' + windowId).modal('openModal');
            jQuery('#' + windowId).applyBindings();
            mageApply.apply(document.getElementById(windowId));
        },

        /**
         * Close dialog window.
         */
        closeDialogWindow: function () {
            jQuery('#' + this.dialogWindowId).modal('closeModal');
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

            if (textareaElm) {
                scrollPos = textareaElm.scrollTop;
                updateElementAtCursor(textareaElm, value);
                textareaElm.focus();
                textareaElm.scrollTop = scrollPos;
                jQuery(textareaElm).change();
                textareaElm = null;
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
        loadChooser: function (url, textareaId) {
            this.textareaId = textareaId;

            // var modal = registry.get('variables_modal.variables_modal.insert_variables', function (modal) {
            //     modal.openModal();
            // });
            if (this.variables == null) {
                new Ajax.Request(url, {
                    parameters: {},
                    onComplete: function (transport) {
                        //if (transport.responseText.isJSON()) {
                            Variables.init(null, 'MagentovariablePlugin.insertVariable');
                            this.variables = transport.responseText;
                            this.openChooser(this.variables);
                        //}
                    }.bind(this)
                });
            } else {
                this.openChooser(this.variables);
            }

            return;
        },

        /**
         * @param {*} variables
         */
        openChooser: function (variables) {
            Variables.openVariableChooser(variables);
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
                this.editor.execCommand('mceInsertContent', false, value);
            }

            return;
        }
    };
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global setLocation, Base64, updateElementAtCursor, varienGlobalEvents */
/* eslint-disable strict */
define([
    'jquery',
    'wysiwygAdapter',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation',
    'mage/adminhtml/events',
    'prototype',
    'Magento_Ui/js/modal/modal'
], function (jQuery, wysiwyg, alert) {
    var widgetTools = {

        /**
         * Sets the widget to be active and is the scope of the slide out if the value is set
         */
        activeSelectedNode: null,
        editMode: false,
        cursorLocation: 0,

        /**
         * Set active selected node.
         *
         * @param {Object} activeSelectedNode
         */
        setActiveSelectedNode: function (activeSelectedNode) {
            this.activeSelectedNode = activeSelectedNode;
        },

        /**
         * Get active selected node.
         *
         * @returns {null}
         */
        getActiveSelectedNode: function () {
            return this.activeSelectedNode;
        },

        /**
         *
         * @param {Boolean} editMode
         */
        setEditMode: function (editMode) {
            this.editMode = editMode;
        },

        /**
         * @param {*} id
         * @param {*} html
         * @return {String}
         */
        getDivHtml: function (id, html) {

            if (!html) {
                html = '';
            }

            return '<div id="' + id + '">' + html + '</div>';
        },

        /**
         * @param {Object} transport
         */
        onAjaxSuccess: function (transport) {
            var response;

            if (transport.responseText.isJSON()) {
                response = transport.responseText.evalJSON();

                if (response.error) {
                    throw response;
                } else if (response.ajaxExpired && response.ajaxRedirect) {
                    setLocation(response.ajaxRedirect);
                }
            }
        },

        dialogOpened: false,

        /**
         * @return {Number}
         */
        getMaxZIndex: function () {
            var max = 0,
                cn = document.body.childNodes,
                i, el, zIndex;

            for (i = 0; i < cn.length; i++) {
                el = cn[i];
                zIndex = el.nodeType == 1 ? parseInt(el.style.zIndex, 10) || 0 : 0; //eslint-disable-line eqeqeq

                if (zIndex < 10000) {
                    max = Math.max(max, zIndex);
                }
            }

            return max + 10;
        },

        /**
         * @param {String} widgetUrl
         */
        openDialog: function (widgetUrl) {
            var oThis = this,
                title = 'Insert Widget',
                mode = 'new',
                dialog;

            if (this.editMode) {
                title = 'Edit Widget';
                mode = 'edit';
            }

            if (this.dialogOpened) {
                return;
            }

            this.dialogWindow = jQuery('<div/>').modal({

                title: jQuery.mage.__(title),
                type: 'slide',
                buttons: [],

                /**
                 * Opened.
                 */
                opened: function () {
                    dialog = jQuery(this).addClass('loading magento-message');

                    widgetUrl += 'mode/' + mode;

                    new Ajax.Updater($(this), widgetUrl, {
                        evalScripts: true,

                        /**
                         * On complete.
                         */
                        onComplete: function () {
                            dialog.removeClass('loading');
                        }
                    });
                },

                /**
                 * @param {jQuery.Event} e
                 * @param {Object} modal
                 */
                closed: function (e, modal) {
                    modal.modal.remove();
                    oThis.dialogOpened = false;
                }
            });

            this.dialogOpened = true;
            this.dialogWindow.modal('openModal');
        }
    },
    WysiwygWidget = {};

    WysiwygWidget.Widget = Class.create();
    WysiwygWidget.Widget.prototype = {
        /**
         * @param {HTMLElement} formEl
         * @param {HTMLElement} widgetEl
         * @param {*} widgetOptionsEl
         * @param {*} optionsSourceUrl
         * @param {*} widgetTargetId
         */
        initialize: function (formEl, widgetEl, widgetOptionsEl, optionsSourceUrl, widgetTargetId) {
            $(formEl).insert({
                bottom: widgetTools.getDivHtml(widgetOptionsEl)
            });

            this.formEl = formEl;
            this.widgetEl = $(widgetEl);
            this.widgetOptionsEl = $(widgetOptionsEl);
            this.optionsUrl = optionsSourceUrl;
            this.optionValues = new Hash({});
            this.widgetTargetId = widgetTargetId;

            if (typeof wysiwyg != 'undefined' && wysiwyg.activeEditor()) { //eslint-disable-line eqeqeq
                this.bMark = wysiwyg.activeEditor().selection.getBookmark();
            }

            // disable -- Please Select -- option from being re-selected
            this.widgetEl.querySelector('option').setAttribute('disabled', 'disabled');

            Event.observe(this.widgetEl, 'change', this.loadOptions.bind(this));

            this.initOptionValues();
        },

        /**
         * @return {String}
         */
        getOptionsContainerId: function () {
            return this.widgetOptionsEl.id + '_' + this.widgetEl.value.gsub(/\//, '_');
        },

        /**
         * @param {*} containerId
         */
        switchOptionsContainer: function (containerId) {
            $$('#' + this.widgetOptionsEl.id + ' div[id^=' + this.widgetOptionsEl.id + ']').each(function (e) {
                this.disableOptionsContainer(e.id);
            }.bind(this));

            if (containerId != undefined) { //eslint-disable-line eqeqeq
                this.enableOptionsContainer(containerId);
            }
            this._showWidgetDescription();
        },

        /**
         * @param {*} containerId
         */
        enableOptionsContainer: function (containerId) {
            $$('#' + containerId + ' .widget-option').each(function (e) {
                e.removeClassName('skip-submit');

                if (e.hasClassName('obligatory')) {
                    e.removeClassName('obligatory');
                    e.addClassName('required-entry');
                }
            });
            $(containerId).removeClassName('no-display');
        },

        /**
         * @param {*} containerId
         */
        disableOptionsContainer: function (containerId) {

            if ($(containerId).hasClassName('no-display')) {
                return;
            }
            $$('#' + containerId + ' .widget-option').each(function (e) {
                // Avoid submitting fields of unactive container
                if (!e.hasClassName('skip-submit')) {
                    e.addClassName('skip-submit');
                }
                // Form validation workaround for unactive container
                if (e.hasClassName('required-entry')) {
                    e.removeClassName('required-entry');
                    e.addClassName('obligatory');
                }
            });
            $(containerId).addClassName('no-display');
        },

        /**
         * Assign widget options values when existing widget selected in WYSIWYG.
         *
         * @return {Boolean}
         */
        initOptionValues: function () {
            var e, widgetCode;

            if (!this.wysiwygExists()) {
                return false;
            }

            e = this.getWysiwygNode();

            if (e.localName === 'span') {
                e = e.firstElementChild;
            }

            if (e != undefined && e.id) { //eslint-disable-line eqeqeq
                // attempt to Base64-decode id on selected node; exception is thrown if it is in fact not a widget node
                try {
                    widgetCode = Base64.idDecode(e.id);
                } catch (ex) {
                    return false;
                }

                if (widgetCode.indexOf('{{widget') !== -1) {
                    this.optionValues = new Hash({});
                    widgetCode.gsub(/([a-z0-9\_]+)\s*\=\s*[\"]{1}([^\"]+)[\"]{1}/i, function (match) {

                        if (match[1] == 'type') { //eslint-disable-line eqeqeq
                            this.widgetEl.value = match[2];
                        } else {
                            this.optionValues.set(match[1], match[2]);
                        }

                    }.bind(this));

                    this.loadOptions();
                }
            }
        },

        /**
         * Load options.
         */
        loadOptions: function () {
            var optionsContainerId,
                params,
                msg,
                msgTmpl,
                $wrapper,
                typeName = this.optionValues.get('type_name');

            if (!this.widgetEl.value) {
                if (typeName) {
                    msgTmpl = jQuery.mage.__('The widget %1 is no longer available. Select a different widget.');
                    msg = jQuery.mage.__(msgTmpl).replace('%1', typeName);

                    jQuery('body').notification('clear').notification('add', {
                        error: true,
                        message: msg,

                        /**
                         * @param {String} message
                         */
                        insertMethod: function (message) {
                            $wrapper = jQuery('<div/>').html(message);

                            $wrapper.insertAfter('.modal-slide .page-main-actions');
                        }
                    });
                }
                this.switchOptionsContainer();

                return;
            }

            optionsContainerId = this.getOptionsContainerId();

            if ($(optionsContainerId) != undefined) { //eslint-disable-line eqeqeq
                this.switchOptionsContainer(optionsContainerId);

                return;
            }

            this._showWidgetDescription();

            params = {
                'widget_type': this.widgetEl.value,
                values: this.optionValues
            };
            new Ajax.Request(this.optionsUrl, {
                parameters: {
                    widget: Object.toJSON(params)
                },

                /**
                 * On success.
                 */
                onSuccess: function (transport) {
                    try {
                        widgetTools.onAjaxSuccess(transport);
                        this.switchOptionsContainer();

                        if ($(optionsContainerId) == undefined) { //eslint-disable-line eqeqeq
                            this.widgetOptionsEl.insert({
                                bottom: widgetTools.getDivHtml(optionsContainerId, transport.responseText)
                            });
                        } else {
                            this.switchOptionsContainer(optionsContainerId);
                        }
                    } catch (e) {
                        alert({
                            content: e.message
                        });
                    }
                }.bind(this)
            });
        },

        /**
         * @private
         */
        _showWidgetDescription: function () {
            var noteCnt = this.widgetEl.next().down('small'),
                descrCnt = $('widget-description-' + this.widgetEl.selectedIndex),
                description;

            if (noteCnt != undefined) { //eslint-disable-line eqeqeq
                description = descrCnt != undefined ? descrCnt.innerHTML : ''; //eslint-disable-line eqeqeq
                noteCnt.update(description);
            }
        },

        /**
         * Validate field.
         */
        validateField: function () {
            jQuery(this.widgetEl).valid();
            jQuery('#insert_button').removeClass('disabled');
        },

        /**
         * Closes the modal
         */
        closeModal: function () {
            widgetTools.dialogWindow.modal('closeModal');
        },

        /* eslint-disable max-depth*/
        /**
         * Insert widget.
         */
        insertWidget: function () {
            var validationResult,
                $form = jQuery('#' + this.formEl),
                formElements,
                i,
                params,
                editor,
                activeNode;

            // remove cached validator instance, which caches elements to validate
            jQuery.data($form[0], 'validator', null);

            $form.validate({
                /**
                 * Ignores elements with .skip-submit, .no-display ancestor elements
                 */
                ignore: function () {
                    return jQuery(this).closest('.skip-submit, .no-display').length;
                },
                errorClass: 'mage-error'
            });

            validationResult = $form.valid();

            if (validationResult) {
                formElements = [];
                i = 0;
                Form.getElements($(this.formEl)).each(function (e) {

                    if (!e.hasClassName('skip-submit')) {
                        formElements[i] = e;
                        i++;
                    }
                });

                // Add as_is flag to parameters if wysiwyg editor doesn't exist
                params = Form.serializeElements(formElements);

                if (!this.wysiwygExists()) {
                    params += '&as_is=1';
                }

                new Ajax.Request($(this.formEl).action, {
                    parameters: params,
                    onComplete: function (transport) {
                        try {
                            editor = wysiwyg.get(this.widgetTargetId);

                            widgetTools.onAjaxSuccess(transport);
                            widgetTools.dialogWindow.modal('closeModal');

                            if (editor) {
                                editor.focus();
                                activeNode = widgetTools.getActiveSelectedNode();

                                if (activeNode) {
                                    editor.selection.select(activeNode);
                                    editor.selection.setContent(transport.responseText);
                                } else if (this.bMark) {
                                    editor.selection.moveToBookmark(this.bMark);
                                }
                            }

                            if (!activeNode) {
                                this.updateContent(transport.responseText);
                            }
                        } catch (e) {
                            alert({
                                content: e.message
                            });
                        }
                    }.bind(this)
                });
            }
        },

        /**
         * @param {Object} content
         */
        updateContent: function (content) {
            var textarea;

            if (this.wysiwygExists()) {
                wysiwyg.insertContent(content, false);
            } else {
                textarea = document.getElementById(this.widgetTargetId);
                updateElementAtCursor(textarea, content);
                varienGlobalEvents.fireEvent('tinymceChange');
                jQuery(textarea).change();
            }
        },

        /**
         * @return {Boolean}
         */
        wysiwygExists: function () {
            return typeof wysiwyg != 'undefined' && wysiwyg.get(this.widgetTargetId);
        },

        /**
         * @return {null|wysiwyg.Editor|*}
         */
        getWysiwyg: function () {
            return wysiwyg.get(this.widgetTargetId);
        },

        /**
         * @return {*|Element}
         */
        getWysiwygNode: function () {
            return widgetTools.getActiveSelectedNode() || wysiwyg.activeEditor().selection.getNode();
        }
    };

    WysiwygWidget.chooser = Class.create();
    WysiwygWidget.chooser.prototype = {

        // HTML element A, on which click event fired when choose a selection
        chooserId: null,

        // Source URL for Ajax requests
        chooserUrl: null,

        // Chooser config
        config: null,

        // Chooser dialog window
        dialogWindow: null,

        // Chooser content for dialog window
        dialogContent: null,

        overlayShowEffectOptions: null,
        overlayHideEffectOptions: null,

        /**
         * @param {*} chooserId
         * @param {*} chooserUrl
         * @param {*} config
         */
        initialize: function (chooserId, chooserUrl, config) {
            this.chooserId = chooserId;
            this.chooserUrl = chooserUrl;
            this.config = config;
        },

        /**
         * @return {String}
         */
        getResponseContainerId: function () {
            return 'responseCnt' + this.chooserId;
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getChooserControl: function () {
            return $(this.chooserId + 'control');
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getElement: function () {
            return $(this.chooserId + 'value');
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getElementLabel: function () {
            return $(this.chooserId + 'label');
        },

        /**
         * Open.
         */
        open: function () {
            $(this.getResponseContainerId()).show();
        },

        /**
         * Close.
         */
        close: function () {
            $(this.getResponseContainerId()).hide();
            this.closeDialogWindow();
        },

        /**
         * Choose.
         */
        choose: function () {
            // Open dialog window with previously loaded dialog content
            var responseContainerId;

            if (this.dialogContent) {
                this.openDialogWindow(this.dialogContent);

                return;
            }
            // Show or hide chooser content if it was already loaded
            responseContainerId = this.getResponseContainerId();

            // Otherwise load content from server
            new Ajax.Request(this.chooserUrl, {
                parameters: {
                    'element_value': this.getElementValue(),
                    'element_label': this.getElementLabelText()
                },

                /**
                 * On success.
                 */
                onSuccess: function (transport) {
                    try {
                        widgetTools.onAjaxSuccess(transport);
                        this.dialogContent = widgetTools.getDivHtml(responseContainerId, transport.responseText);
                        this.openDialogWindow(this.dialogContent);
                    } catch (e) {
                        alert({
                            content: e.message
                        });
                    }
                }.bind(this)
            });
        },

        /**
         * Open dialog winodw.
         *
         * @param {*} content
         */
        openDialogWindow: function (content) {
            this.dialogWindow = jQuery('<div/>').modal({
                title: this.config.buttons.open,
                type: 'slide',
                buttons: [],

                /**
                 * Opened.
                 */
                opened: function () {
                    jQuery(this).addClass('magento-message');
                },

                /**
                 * @param {jQuery.Event} e
                 * @param {Object} modal
                 */
                closed: function (e, modal) {
                    modal.modal.remove();
                    this.dialogWindow = null;
                }
            });

            this.dialogWindow.modal('openModal').append(content);
        },

        /**
         * Close dialog window.
         */
        closeDialogWindow: function () {
            this.dialogWindow.modal('closeModal').remove();
        },

        /**
         * @return {*|Number}
         */
        getElementValue: function () {
            return this.getElement().value;
        },

        /**
         * @return {String}
         */
        getElementLabelText: function () {
            return this.getElementLabel().innerHTML;
        },

        /**
         * @param {*} value
         */
        setElementValue: function (value) {
            this.getElement().value = value;
        },

        /**
         * @param {*} value
         */
        setElementLabel: function (value) {
            this.getElementLabel().innerHTML = value;
        }
    };

    window.WysiwygWidget = WysiwygWidget;
    window.widgetTools = widgetTools;
});

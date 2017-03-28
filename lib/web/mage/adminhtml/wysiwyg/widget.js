/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global setLocation, Base64, updateElementAtCursor, varienGlobalEvents */
/* eslint-disable strict */
define([
    'jquery',
    'tinymce',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation',
    'mage/adminhtml/events',
    'prototype',
    'Magento_Ui/js/modal/modal'
], function (jQuery, tinyMCE, alert) {
    var widgetTools = {
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
                var oThis = this;

                if (this.dialogOpened) {
                    return;
                }

                this.dialogWindow = jQuery('<div/>').modal({
                    title: jQuery.mage.__('Insert Widget...'),
                    type: 'slide',
                    buttons: [],

                    /**
                     * Opened.
                     */
                    opened: function () {
                        var dialog = jQuery(this).addClass('loading magento-message');

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
            jQuery('#' + formEl).mage('validation', {
                ignore: '.skip-submit',
                errorClass: 'mage-error'
            });
            this.formEl = formEl;
            this.widgetEl = $(widgetEl);
            this.widgetOptionsEl = $(widgetOptionsEl);
            this.optionsUrl = optionsSourceUrl;
            this.optionValues = new Hash({});
            this.widgetTargetId = widgetTargetId;

            if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor) { //eslint-disable-line eqeqeq
                this.bMark = tinyMCE.activeEditor.selection.getBookmark();
            }

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

            if (e != undefined && e.id) { //eslint-disable-line eqeqeq
                widgetCode = Base64.idDecode(e.id);

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
            var optionsContainerId, params;

            if (!this.widgetEl.value) {
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
        },

        /**
         * Insert widget.
         */
        insertWidget: function () {
            var validationResult, formElements, i, params;

            jQuery('#' + this.formEl).validate({
                ignore: '.skip-submit',
                errorClass: 'mage-error'
            });

            validationResult = jQuery('#' + this.formEl).valid();

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
                            widgetTools.onAjaxSuccess(transport);
                            widgetTools.dialogWindow.modal('closeModal');

                            if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor) {
                                tinyMCE.activeEditor.focus();

                                if (this.bMark) { //eslint-disable-line max-depth
                                    tinyMCE.activeEditor.selection.moveToBookmark(this.bMark);
                                }
                            }

                            this.updateContent(transport.responseText);
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
                this.getWysiwyg().execCommand('mceInsertContent', false, content);
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
            return typeof tinyMCE != 'undefined' && tinyMCE.get(this.widgetTargetId);
        },

        /**
         * @return {null|tinymce.Editor|*}
         */
        getWysiwyg: function () {
            return tinyMCE.activeEditor;
        },

        /**
         * @return {*|Element}
         */
        getWysiwygNode: function () {
            return tinyMCE.activeEditor.selection.getNode();
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

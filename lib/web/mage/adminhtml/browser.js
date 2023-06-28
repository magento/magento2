/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global MediabrowserUtility, FORM_KEY, tinyMceEditors */
/* eslint-disable strict */
define([
    'jquery',
    'wysiwygAdapter',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
    'jquery/jstree/jquery.jstree',
    'mage/mage'
], function ($, wysiwyg, prompt, confirm, alert, _) {
    window.MediabrowserUtility = {
        windowId: 'modal_dialog_message',
        modalLoaded: false,
        targetElementId: false,
        pathId: '',

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
         * @param {*} url
         * @param {*} width
         * @param {*} height
         * @param {*} title
         * @param {Object} options
         */
        openDialog: function (url, width, height, title, options) {
            var windowId = this.windowId,
                content = '<div class="popup-window" id="' + windowId + '"></div>';

            if (this.modalLoaded) {

                if (!_.isUndefined(options)) {
                    this.modal.modal('option', 'closed', options.closed);
                }

                this.modal.modal('openModal');
                this.setTargetElementId(options, url);
                this.setPathId(url);
                $(window).trigger('reload.MediaGallery');

                return;
            }

            this.modal = $(content).modal($.extend({
                title:  title || 'Insert File...',
                modalClass: 'magento',
                type: 'slide',
                buttons: []
            }, options));

            this.modal.modal('openModal');

            $.ajax({
                url: url,
                type: 'get',
                context: $(this),
                showLoader: true

            }).done(function (data) {
                this.modal.html(data).trigger('contentUpdated');
                this.modalLoaded = true;
                this.setTargetElementId(options, url);
                this.setPathId(url);
            }.bind(this));

        },

        /**
         * Setter for endcoded path id
         */
        setPathId: function (url) {
            this.pathId = url.match(/(&|\/|%26)current_tree_path(=|\/)([\s\S].*?)(\/|$)/)[3];
        },

        /**
         * Setter for targetElementId property
         *
         * @param {Object} options
         * @param {String} url
         */
        setTargetElementId: function (options, url) {
            this.targetElementId = options && options.targetElementId ?
                options.targetElementId
                : url.match(/\/target_element_id\/([\s\S].*?)\//)[1];
        },

        /**
         * Close dialog.
         */
        closeDialog: function () {
            this.modal.modal('closeModal');
        }
    };

    $.widget('mage.mediabrowser', {
        eventPrefix: 'mediabrowser',
        options: {
            contentsUrl: null,
            onInsertUrl: null,
            newFolderUrl: null,
            deleteFolderUrl: null,
            deleteFilesUrl: null,
            headerText: null,
            tree: null,
            currentNode: null,
            storeId: null,
            showBreadcrumbs: null,
            hidden: 'no-display'
        },

        /**
         * Proxy creation
         * @protected
         */
        _create: function () {
            this._on({
                'click [data-row=file]': 'selectFile',
                'dblclick [data-row=file]': 'insert',
                'click #new_folder': 'newFolder',
                'click #delete_folder': 'deleteFolder',
                'click #delete_files': 'deleteFiles',
                'click #insert_files': 'insertSelectedFiles',
                'fileuploaddone': '_uploadDone',
                'click [data-row=breadcrumb]': 'selectFolder'
            });

            $(window).on('reload.MediaGallery', $.proxy(this.reload, this));
            this.activeNode = null;
            //tree dont use event bubbling
            this.tree = this.element.find('[data-role=tree]');
            this.tree.on('select_node.jstree', $.proxy(this._selectNode, this));
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _selectNode: function (event, data) {
            var node = data.node;

            this.activeNode = node;
            this.element.find('#delete_files, #insert_files').toggleClass(this.options.hidden, true);
            this.element.find('#contents').toggleClass(this.options.hidden, false);
            this.element.find('#delete_folder')
                .toggleClass(this.options.hidden, node.id === 'root'); //eslint-disable-line eqeqeq
            this.element.find('#content_header_text')
                .html(node.id === 'root' ? this.headerText : node.text); //eslint-disable-line eqeqeq

            this.drawBreadcrumbs(data);
            this.loadFileList(node);
        },

        /**
         * @return {*}
         */
        reload: function (uploaded) {
            return this.loadFileList(this.activeNode, uploaded);
        },

        /**
         * @param {Object} element
         * @param {*} value
         */
        insertAtCursor: function (element, value) {
            var sel, startPos, endPos, scrollTop;

            if ('selection' in document) {
                //For browsers like Internet Explorer
                element.focus();
                sel = document.selection.createRange();
                sel.text = value;
                element.focus();
            } else if (element.selectionStart || element.selectionStart == '0') { //eslint-disable-line eqeqeq
                //For browsers like Firefox and Webkit based
                startPos = element.selectionStart;
                endPos = element.selectionEnd;
                scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) + value +
                    element.value.substring(startPos, endPos) + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length + element.value.substring(startPos, endPos).length;
                element.scrollTop = scrollTop;
            } else {
                element.value += value;
                element.focus();
            }
        },

        /**
         * @param {Object} node
         */
        loadFileList: function (node, uploaded) {
            var contentBlock = this.element.find('#contents');

            return $.ajax({
                url: this.options.contentsUrl,
                type: 'GET',
                dataType: 'html',
                data: {
                    'form_key': FORM_KEY,
                    node: node ? node.id : null
                },
                context: contentBlock,
                showLoader: true
            }).done(function (data) {
                contentBlock.html(data).trigger('contentUpdated');

                if (uploaded) {
                    contentBlock.find('.filecnt:last').trigger('click');
                }
            });
        },

        /**
         * @param {jQuery.Event} event
         */
        selectFolder: function (event) {
            this.element.find('[data-id="' + $(event.currentTarget).data('node').id + '"]>a').trigger('click');
        },

        /**
         * Insert selected files.
         */
        insertSelectedFiles: function () {
            this.element.find('[data-row=file].selected').trigger('dblclick');
        },

        /**
         * @param {jQuery.Event} event
         */
        selectFile: function (event) {
            var fileRow = $(event.currentTarget);

            fileRow.toggleClass('selected');
            this.element.find('[data-row=file]').not(fileRow).removeClass('selected');
            this.element.find('#delete_files, #insert_files')
                .toggleClass(this.options.hidden, !fileRow.is('.selected'));
            fileRow.trigger('selectfile');
        },

        /**
         * @private
         */
        _uploadDone: function () {
            this.element.find('.file-row').remove();
            this.reload(true);
        },

        /**
         * @param {jQuery.Event} event
         * @return {Boolean}
         */
        insert: function (event) {
            var fileRow = $(event.currentTarget),
                targetEl;

            if (!fileRow.prop('id')) {
                return false;
            }
            targetEl = this.getTargetElement();

            if (!targetEl.length) {
                MediabrowserUtility.closeDialog();
                throw 'Target element not found for content update';
            }

            return $.ajax({
                url: this.options.onInsertUrl,
                data: {
                    filename: fileRow.attr('id'),
                    node: this.activeNode.id,
                    store: this.options.storeId,
                    'as_is': typeof targetEl !== 'function' && targetEl.is('textarea') ? 1 : 0,
                    'force_static_path': typeof targetEl !== 'function' && targetEl.data('force_static_path') ? 1 : 0,
                    'form_key': FORM_KEY
                },
                context: this,
                showLoader: true
            }).done($.proxy(function (data) {
                if (typeof targetEl === 'function') {
                    targetEl(data, {text: fileRow.find('img').attr('alt')});
                } else if (targetEl.is('textarea')) {
                    this.insertAtCursor(targetEl.get(0), data);
                } else {
                    targetEl
                        .val(data)
                        .data('size', fileRow.data('size'))
                        .data('mime-type', fileRow.data('mime-type'));
                }
                MediabrowserUtility.closeDialog();

                if (typeof targetEl !== 'function') {
                    targetEl.focus();
                    jQuery(targetEl).trigger('change');
                }
            }, this));
        },

        /**
         * Find document target element in next order:
         *  in acive file browser opener:
         *  - input field with ID: "src" in opener window
         *  - input field with ID: "href" in opener window
         *  in document:
         *  - element with target ID
         *
         * return {HTMLElement|null}
         */
        getTargetElement: function () {
            var mediaBrowser = window.MediabrowserUtility;

            if (!_.isUndefined(wysiwyg) && wysiwyg.get(mediaBrowser.targetElementId)) {
                return this.getMediaBrowserOpener() || window;
            }

            return $('#' + mediaBrowser.targetElementId);
        },

        /**
         * Return opener Window object if it exists, not closed and editor is active
         *
         * return {Object|null}
         */
        getMediaBrowserOpener: function () {
            var targetElementId = window.MediabrowserUtility.targetElementId;

            if (!_.isUndefined(wysiwyg) && wysiwyg.get(targetElementId) && !_.isUndefined(tinyMceEditors)) {
                return tinyMceEditors.get(targetElementId).getMediaBrowserOpener();
            }

            return null;
        },

        /**
         * New folder.
         */
        newFolder: function () {
            var self = this;

            prompt({
                title: this.options.newFolderPrompt,
                actions: {
                    /**
                     * @param {*} folderName
                     */
                    confirm: function (folderName) {
                        $.ajax({
                            url: self.options.newFolderUrl,
                            dataType: 'json',
                            data: {
                                name: folderName,
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function (data) {
                            if (data.error) {
                                alert({
                                    content: data.message
                                });
                            } else {
                                self.tree.jstree(
                                    'refresh_node',
                                    self.element.find('[data-id="' + self.activeNode.id + '"]')
                                );
                            }
                        }, this));

                        return true;
                    }
                }
            });
        },

        /**
         * Delete folder.
         */
        deleteFolder: function () {
            var self = this;

            confirm({
                content: this.options.deleteFolderConfirmationMessage,
                actions: {
                    /**
                     * Confirm.
                     */
                    confirm: function () {
                        return $.ajax({
                            url: self.options.deleteFolderUrl,
                            dataType: 'json',
                            data: {
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function (data) {
                            if (data.error) {
                                alert({
                                    content: data.message
                                });
                            } else {
                                self.tree.jstree('refresh', self.activeNode.id);
                                self.reload();
                                $(window).trigger('fileDeleted.mediabrowser', {
                                    ids: self.activeNode.id
                                });
                            }
                        }, this));
                    },

                    /**
                     * @return {Boolean}
                     */
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        /**
         * Delete files.
         */
        deleteFiles: function () {
            var self = this;

            confirm({
                content: this.options.deleteFileConfirmationMessage,
                actions: {
                    /**
                     * Confirm.
                     */
                    confirm: function () {
                        var selectedFiles = self.element.find('[data-row=file].selected'),
                            ids = selectedFiles.map(function () {
                                return $(this).attr('id');
                            }).toArray();

                        return $.ajax({
                            url: self.options.deleteFilesUrl,
                            data: {
                                files: ids,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function (data) {
                            if (data.error) {
                                alert({
                                    content: data.message
                                });
                            } else {
                                self.reload();
                                self.element.find('#delete_files, #insert_files').toggleClass(
                                    self.options.hidden, true
                                );

                                $(window).trigger('fileDeleted.mediabrowser', {
                                    ids: ids
                                });
                            }
                        }, this));
                    },

                    /**
                     * @return {Boolean}
                     */
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        /**
         * @param {Object} data
         */
        drawBreadcrumbs: function (data) {
            var node, breadcrumbs;

            if (this.element.find('#breadcrumbs').length) {
                this.element.find('#breadcrumbs').remove();
            }
            node = data.node;

            if (node.id === 'root') { //eslint-disable-line eqeqeq
                return;
            }
            breadcrumbs = $('<ul class="breadcrumbs" id="breadcrumbs"></ul>');
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            data.instance.get_path(node).each(function (name, index) {
                if (index > 0) {
                    breadcrumbs.append($('<li>\/</li>')); //eslint-disable-line
                }
                breadcrumbs.append($('<li />').attr('data-row', 'breadcrumb').text(name));

            });
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            breadcrumbs.insertAfter(this.element.find('#content_header'));
        }
    });

    return window.MediabrowserUtility;
});

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "tinymce",
    "Magento_Ui/js/modal/prompt",
    "Magento_Ui/js/modal/confirm",
    "Magento_Ui/js/modal/alert",
    "Magento_Ui/js/modal/modal",
    "jquery/ui",
    "jquery/jstree/jquery.jstree",
    "mage/mage"
], function($, tinyMCEm, prompt, confirm, alert){
    
    MediabrowserUtility = {
        windowId: 'modal_dialog_message',
        getMaxZIndex: function() {
            var max = 0, i;
            var cn = document.body.childNodes;
            for (i = 0; i < cn.length; i++) {
                var el = cn[i];
                var zIndex = el.nodeType == 1 ? parseInt(el.style.zIndex, 10) || 0 : 0;
                if (zIndex < 10000) {
                    max = Math.max(max, zIndex);
                }
            }
            return max + 10;
        },
        openDialog: function(url, width, height, title, options) {
            var windowId = this.windowId,
                content = '<div class="popup-window magento_message" "id="' + windowId + '"></div>',
                self = this;

            if (this.modal) {
                this.modal.html($(content).html());
            } else {
                this.modal = $(content).modal($.extend({
                    title:  title || 'Insert File...',
                    modalClass: 'magento',
                    type: 'slide',
                    buttons: []
                }, options));
            }
            this.modal.modal('openModal');
            $.ajax({
                url: url,
                type: 'get',
                context: $(this),
                showLoader: true

            }).done(function(data) {
                self.modal.html(data).trigger('contentUpdated');
            });
        },
        closeDialog: function() {
            this.modal.modal('closeModal');
        }
    };

    $.widget("mage.mediabrowser", {
        eventPrefix: "mediabrowser",
        options: {
            targetElementId: null,
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
        _create: function() {
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
            this.activeNode = null;
            //tree dont use event bubbling
            this.tree = this.element.find('[data-role=tree]');
            this.tree.on("select_node.jstree", $.proxy(this._selectNode, this));
        },

        _selectNode: function(event, data) {
            var node = data.rslt.obj.data('node');
            this.activeNode = node;
            this.element.find('#delete_files, #insert_files').toggleClass(this.options.hidden, true);
            this.element.find('#contents').toggleClass(this.options.hidden, false);
            this.element.find('#delete_folder').toggleClass(this.options.hidden, node.id == 'root');
            this.element.find('#content_header_text').html(node.id == 'root' ? this.headerText : node.text);

            this.drawBreadcrumbs(data);
            this.loadFileList(node);
        },

        reload : function() {
            return this.loadFileList(this.activeNode);
        },

        insertAtCursor: function(element, value) {
            if ('selection' in document) {
                //For browsers like Internet Explorer
                element.focus();
                sel = document.selection.createRange();
                sel.text = value;
                element.focus();
            } else if (element.selectionStart || element.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = element.selectionStart;
                var endPos = element.selectionEnd;
                var scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) + value + element.value.substring(startPos, endPos)
                    + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = ((startPos + value.length) + element.value.substring(startPos, endPos).length);
                element.scrollTop = scrollTop;
            } else {
                element.value += value;
                element.focus();
            }
        },

        loadFileList: function(node) {
            var contentBlock = this.element.find('#contents');
            return $.ajax({
                url: this.options.contentsUrl,
                type: 'GET',
                dataType: 'html',
                data: {
                    form_key: FORM_KEY,
                    node: node.id
                },
                context: contentBlock,
                showLoader: true
            }).done(function(data) {
                contentBlock.html(data).trigger('contentUpdated');
            });
        },

        selectFolder: function(event) {
            this.element.find('[data-id="'+ $(event.currentTarget).data('node').id +'"]>a').click();
        },

        insertSelectedFiles: function(event) {
            this.element.find('[data-row=file].selected').trigger('dblclick');
        },

        selectFile: function(event) {
            var fileRow = $(event.currentTarget);
            fileRow.toggleClass('selected');
            this.element.find('[data-row=file]').not(fileRow).removeClass('selected');
            this.element.find('#delete_files, #insert_files')
                .toggleClass(this.options.hidden, !fileRow.is('.selected'));
            fileRow.trigger('selectfile');
        },

        _uploadDone: function(event) {
            this.element.find('.file-row').remove();
            this.reload();
        },

        insert: function(event) {
            var fileRow = $(event.currentTarget);

            if (!fileRow.prop('id')) {
                return false;
            }
            var targetEl = this.getTargetElement();

            if (!targetEl.length) {
                MediabrowserUtility.closeDialog();
                throw "Target element not found for content update";
            }

            return $.ajax({
                url: this.options.onInsertUrl,
                data: {
                    filename: fileRow.attr('id'),
                    node: this.activeNode.id,
                    store: this.options.storeId,
                    as_is: targetEl.is('textarea') ? 1 : 0,
                    form_key: FORM_KEY
                },
                context: this,
                showLoader: true
            }).done($.proxy(function(data) {
                if (targetEl.is('textarea')) {
                    this.insertAtCursor(targetEl.get(0), data);
                } else {
                    targetEl.val(data).trigger('change');
                }
                MediabrowserUtility.closeDialog();
                targetEl.focus();
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
         * return HTMLelement | null
         */
        getTargetElement: function() {
            if (typeof(tinyMCE) != 'undefined' && tinyMCE.get(this.options.targetElementId)) {
                var opener = this.getMediaBrowserOpener() || window;
                var targetElementId = tinyMceEditors.get(this.options.targetElementId).getMediaBrowserTargetElementId();
                return $(opener.document.getElementById(targetElementId));
            } else {
                return $('#' + this.options.targetElementId);
            }
        },

        /**
         * Return opener Window object if it exists, not closed and editor is active
         *
         * return object | null
         */
        getMediaBrowserOpener: function() {
            if (typeof(tinyMCE) != 'undefined'
                && tinyMCE.get(this.options.targetElementId)
                && typeof(tinyMceEditors) != 'undefined'
                && !tinyMceEditors.get(this.options.targetElementId).getMediaBrowserOpener().closed) {
                return tinyMceEditors.get(this.options.targetElementId).getMediaBrowserOpener();
            } else {
                return null;
            }
        },

        newFolder: function() {
            var self = this;

            prompt({
                title: this.options.newFolderPrompt,
                actions: {
                    confirm: function (folderName) {
                        return $.ajax({
                            url: self.options.newFolderUrl,
                            dataType: 'json',
                            data: {
                                name: folderName,
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                form_key: FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function(data) {
                            if (data.error) {
                                alert({
                                    content: data.message
                                });
                            } else {
                                self.tree.jstree('refresh',  self.element.find('[data-id="' + self.activeNode.id + '"]'));
                            }
                        }, this));
                    }
                }
            });
        },

        deleteFolder: function() {
            var self = this;

            confirm({
                content: this.options.deleteFolderConfirmationMessage,
                actions: {
                    confirm: function () {
                        return $.ajax({
                            url: self.options.deleteFolderUrl,
                            dataType: 'json',
                            data: {
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                form_key: FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function(data) {
                            self.tree.jstree('refresh', self.activeNode.id);
                        }, this));
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        deleteFiles: function() {
            var self = this;

            confirm({
                content: this.options.deleteFileConfirmationMessage,
                actions: {
                    confirm: function () {
                        var selectedFiles = self.element.find('[data-row=file].selected');
                        var ids = selectedFiles.map(function(index, file) {
                            return $(this).attr('id');
                        }).toArray();

                        return $.ajax({
                            url: self.options.deleteFilesUrl,
                            data: {
                                files: ids,
                                store: self.options.storeId,
                                form_key: FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function(data) {
                            self.reload();
                        }, this));
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        drawBreadcrumbs: function(data) {
            if (this.element.find('#breadcrumbs').length) {
                this.element.find('#breadcrumbs').remove();
            }
            var node = data.rslt.obj.data('node');
            if (node.id == 'root') {
                return;
            }
            var breadcrumbs = $('<ul class="breadcrumbs" id="breadcrumbs" />');
            $(data.rslt.obj.parents('[data-id]').get().reverse()).add(data.rslt.obj).each(function(index, element){
                var node = $(element).data('node');
                if (index > 0) {
                    breadcrumbs.append($('<li>\/</li>'));
                }
                breadcrumbs.append($('<li />').data('node', node).attr('data-row', 'breadcrumb').text(node.text));

            });

            breadcrumbs.insertAfter(this.element.find('#content_header'))
        }
    });
});

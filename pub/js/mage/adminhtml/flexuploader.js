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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if(!window.Flex) {
    alert('Flex library not loaded');
} else {
    Flex.Uploader = Class.create();
    Flex.Uploader.prototype = {
        flex: null,
        uploader:null,
        filters:null,
        containerId:null,
        flexContainerId:null,
        container:null,
        files:null,
        fileRowTemplate:null,
        fileProgressTemplate:null,
        templatesPattern: /(^|.|\r|\n)(\{\{(.*?)\}\})/,
        onFilesComplete: false,
        onFileProgress: true,
        onFileRemove: false,
        onContainerHideBefore:null,
        initialize: function(containerId, uploaderSrc, config) {
            this.containerId = containerId;
            this.container   = $(containerId);

            this.container.controller = this;

            this.config = config;

            this.flexContainerId = this.containerId + '-flash';
            Element.insert(
                // window.document.body,
                this.containerId,
                {'before':'<div id="'+this.flexContainerId+'" class="flex" style="position:relative;float:right;"></div>'}
            );
            flexWidth = 230;
            if (this.config.width) {
                flexWidth = this.config.width;
            }
            this.flex = new Flex.Object({
                left: 100,
                top: 300,
                width:  flexWidth,
                height: 20,
                src:    uploaderSrc,
                wmode: 'transparent'
            });
            // this.getInnerElement('browse').disabled = true;
            // this.getInnerElement('upload').disabled = true;
            this.fileRowTemplate = new Template(
                this.getInnerElement('template').innerHTML,
                this.templatesPattern
            );

            this.fileProgressTemplate = new Template(
                this.getInnerElement('template-progress').innerHTML,
                this.templatesPattern
            );

            this.flex.onBridgeInit = this.handleBridgeInit.bind(this);
            if (this.flex.detectFlashVersion(9, 0, 28)) {
                this.flex.apply(this.flexContainerId);
            } else {
                // this.getInnerElement('browse').hide();
                // this.getInnerElement('upload').hide();
                this.getInnerElement('install-flash').show();
            }
            this.onContainerHideBefore = this.handleContainerHideBefore.bind(this);
        },
        getInnerElement: function(elementName) {
            return $(this.containerId + '-' + elementName);
        },
        getFileId: function(file) {
            var id;
            if(typeof file == 'object') {
                id = file.id;
            } else {
                id = file;
            }
            return this.containerId + '-file-' + id;
        },
        getDeleteButton: function(file) {
            return $(this.getFileId(file) + '-delete');
        },
        handleBridgeInit: function() {
            this.uploader = this.flex.getBridge().getUpload();
            if (this.config.filters) {
                $H(this.config.filters).each(function(pair) {
                    this.uploader.addTypeFilter(pair.key, pair.value.label, pair.value.files);
                }.bind(this));
                delete(this.config.filters);
                this.uploader.setUseTypeFilter(true);
            }

            this.uploader.setConfig(this.config);
            this.uploader.addEventListener('select',    this.handleSelect.bind(this));
            this.uploader.addEventListener('complete',  this.handleComplete.bind(this));
            this.uploader.addEventListener('progress',  this.handleProgress.bind(this));
            this.uploader.addEventListener('error',     this.handleError.bind(this));
            this.uploader.addEventListener('removeall', this.handleRemoveAll.bind(this));
            // this.getInnerElement('browse').disabled = false;
            // this.getInnerElement('upload').disabled = false;
        },
        browse: function() {
            this.uploader.browse();
        },
        upload: function() {
            this.uploader.upload();
            this.files = this.uploader.getFilesInfo();
            this.updateFiles();
        },
        removeFile: function(id) {
            this.uploader.removeFile(id);
            $(this.getFileId(id)).remove();
            if (this.onFileRemove) {
                this.onFileRemove(id);
            }
            this.files = this.uploader.getFilesInfo();
            this.updateFiles();
        },
        removeAllFiles: function() {
            this.files.each(function(file) {
                this.removeFile(file.id);
            }.bind(this));
            this.files = this.uploader.getFilesInfo();
            this.updateFiles();
        },
        handleSelect: function (event) {
            this.files = event.getData().files;
            this.checkFileSize();
            this.updateFiles();
            this.getInnerElement('upload').show();
            if (this.onFileSelect) {
                this.onFileSelect();
            }
        },
        handleProgress: function (event) {
            var file = event.getData().file;
            this.updateFile(file);
            if (this.onFileProgress) {
                this.onFileProgress(file);
            }
        },
        handleError: function (event) {
            this.updateFile(event.getData().file);
        },
        handleComplete: function (event) {
            this.files = event.getData().files;
            this.updateFiles();
            if (this.onFilesComplete) {
                this.onFilesComplete(this.files);
            }
        },
        handleRemoveAll: function (event) {
            this.files.each(function(file) {
                $(this.getFileId(file.id)).remove();
            }.bind(this));
            if (this.onFileRemoveAll) {
                this.onFileRemoveAll();
            }
            this.files = this.uploader.getFilesInfo();
            this.updateFiles();
        },
        handleRemove: function (event) {
            this.files = this.uploader.getFilesInfo();
            this.updateFiles();
        },
        updateFiles: function () {
            this.files.each(function(file) {
                this.updateFile(file);
            }.bind(this));
        },
        updateFile:  function (file) {
            if (!$(this.getFileId(file))) {
                if (this.config.replace_browse_with_remove) {
                    $(this.containerId+'-new').show();
                    $(this.containerId+'-new').innerHTML = this.fileRowTemplate.evaluate(this.getFileVars(file));
                    $(this.containerId+'-old').hide();
                    this.flex.getBridge().hideBrowseButton();
                } else {
                    Element.insert(this.container, {bottom: this.fileRowTemplate.evaluate(this.getFileVars(file))});
                }
            }
            if (file.status == 'full_complete' && file.response.isJSON()) {
                var response = file.response.evalJSON();
                if (typeof response == 'object') {
                    if (typeof response.cookie == 'object') {
                        var date = new Date();
                        date.setTime(date.getTime()+(parseInt(response.cookie.lifetime)*1000));

                        document.cookie = escape(response.cookie.name) + "="
                            + escape(response.cookie.value)
                            + "; expires=" + date.toGMTString()
                            + (response.cookie.path.blank() ? "" : "; path=" + response.cookie.path)
                            + (response.cookie.domain.blank() ? "" : "; domain=" + response.cookie.domain);
                    }
                    if (typeof response.error != 'undefined' && response.error != 0) {
                        file.status = 'error';
                        file.errorText = response.error;
                    }
                }
            }

            if (file.status == 'full_complete' && !file.response.isJSON()) {
                file.status = 'error';
            }

            var progress = $(this.getFileId(file)).getElementsByClassName('progress-text')[0];
            if ((file.status=='progress') || (file.status=='complete')) {
                $(this.getFileId(file)).addClassName('progress');
                $(this.getFileId(file)).removeClassName('new');
                $(this.getFileId(file)).removeClassName('error');
                if (file.progress && file.progress.total) {
                    progress.update(this.fileProgressTemplate.evaluate(this.getFileProgressVars(file)));
                } else {
                    progress.update('');
                }
                if (! this.config.replace_browse_with_remove) {
                    this.getDeleteButton(file).hide();
                }
            } else if (file.status=='error') {
                $(this.getFileId(file)).addClassName('error');
                $(this.getFileId(file)).removeClassName('progress');
                $(this.getFileId(file)).removeClassName('new');
                var errorText = file.errorText ? file.errorText : this.errorText(file);
                if (this.config.replace_browse_with_remove) {
                    this.flex.getBridge().hideBrowseButton();
                } else {
                    this.getDeleteButton(file).show();
                }

                progress.update(errorText);

            } else if (file.status=='full_complete') {
                $(this.getFileId(file)).addClassName('complete');
                $(this.getFileId(file)).removeClassName('progress');
                $(this.getFileId(file)).removeClassName('error');
                if (this.config.replace_browse_with_remove) {
                    this.flex.getBridge().hideRemoveButton();
                }
                progress.update(this.translate('Complete'));
            }
        },
        getDebugStr: function(obj) {
             return Object.toJSON(obj).replace('&', '&amp;').replace('>', '&gt;').replace('<', '&lt;');
        },
        getFileVars: function(file) {
            return {
                id      : this.getFileId(file),
                fileId  : file.id,
                name    : file.name,
                size    : this.formatSize(file.size)
            };
        },
        getFileProgressVars: function(file) {
            return {
                total    : this.formatSize(file.progress.total),
                uploaded : this.formatSize(file.progress.loaded),
                percent  : this.round((file.progress.loaded/file.progress.total)*100)
            };
        },
        formatSize: function(size) {
            if (size > 1024 * 1024 * 1024 * 1024) {
                return this.round(size / (1024 * 1024 * 1024 * 1024)) + ' ' + this.translate('TB');
            } else if (size > 1024 * 1024 * 1024) {
                return this.round(size / (1024 * 1024 * 1024)) + ' ' + this.translate('GB');
            } else if (size > 1024 * 1024) {
                return this.round(size / (1024 * 1024)) + ' ' + this.translate('MB');
            } else if (size > 1024) {
                return this.round(size / (1024)) + ' ' + this.translate('kB');
            }
            return size + ' ' + this.translate('B');
        },
        round: function(number) {
            return Math.round(number*100)/100;
        },
        checkFileSize: function() {
            newFiles = [];
            hasTooBigFiles = false;
            this.files.each(function(file){
                if (file.size > maxUploadFileSizeInBytes) {
                    hasTooBigFiles = true;
                    this.uploader.removeFile(file.id)
                } else {
                    newFiles.push(file)
                }
            }.bind(this));
            this.files = newFiles;
            if (hasTooBigFiles) {
                alert(
                    this.translate('Maximum allowed file size for upload is')+' '+maxUploadFileSize+".\n"+this.translate('Please check your server PHP settings.')
                );
            }
        },
        translate: function(text) {
            try {
                if(Translator){
                   return Translator.translate(text);
                }
            }
            catch(e){}
            return text;
        },
        errorText: function(file) {
            var error = '';

            switch(file.errorCode) {
                case 1: // Size 0
                    error = 'File size should be more than 0 bytes';
                    break;
                case 2: // Http error
                    error = 'Upload HTTP Error';
                    break;
                case 3: // I/O error
                    error = 'Upload I/O Error';
                    break;
                case 4: // Security error
                    error = 'Upload Security Error';
                    break;
                case 5: // SSL self-signed certificate
                    error = 'SSL Error: Invalid or self-signed certificate';
                    break;
            }

            if(error) {
                return this.translate(error);
            }

            return error;
        },
        handleContainerHideBefore: function(container) {
            if (container && Element.descendantOf(this.container, container) && !this.checkAllComplete()) {
                if (! confirm('There are files that were selected but not uploaded yet. After switching to another tab your selections will be lost. Do you wish to continue ?')) {
                    return 'cannotchange';
                } else {
                    this.removeAllFiles();
                }
            }
        },
        checkAllComplete: function() {
            if (this.files) {
                return !this.files.any(function(file) {
                    return (file.status !== 'full_complete')
                });
            }
            return true;
        }
    }
}

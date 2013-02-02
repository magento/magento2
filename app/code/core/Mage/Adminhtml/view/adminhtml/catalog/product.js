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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var Product = {};

Product.Gallery = Class.create();
Product.Gallery.prototype = {
    images : [],
    file2id : {
        'no_selection' :0
    },
    idIncrement :1,
    containerId :'',
    container :null,
    uploader :null,
    imageTypes : {},
    initialize : function(containerId, uploader, imageTypes) {
        this.containerId = containerId, this.container = $(this.containerId);
        this.uploader = uploader;
        this.imageTypes = imageTypes;
        if (this.uploader) {
            this.uploader.onFilesComplete = this.handleUploadComplete
                    .bind(this);
        }
        // this.uploader.onFileProgress = this.handleUploadProgress.bind(this);
        // this.uploader.onFileError = this.handleUploadError.bind(this);
        this.images = this.getElement('save').value.evalJSON();
        this.imagesValues = this.getElement('save_image').value.evalJSON();
        this.template = new Template('<tr id="__id__" class="preview">' + this
                .getElement('template').innerHTML + '</tr>', new RegExp(
                '(^|.|\\r|\\n)(__([a-zA-Z0-9_]+)__)', ''));
        this.fixParentTable();
        this.updateImages();
        jQuery('#' + this.containerId)
            .closest('.ui-tabs-panel')
            .on('move.tabs', jQuery.proxy(this.onImageTabMove, this));
    },
    onImageTabMove : function(event, tab) {
        var imagesTab = false;
        this.container.ancestors().each( function(parentItem) {
            if (parentItem.tabObject) {
                imagesTab = parentItem.tabObject;
                throw $break;
            }
        }.bind(this));

        if (imagesTab && tab && tab.name && imagesTab.name === tab.name) {
            this.container.select('input[type="radio"]').each(function(radio) {
                radio.observe('change', this.onChangeRadio);
            }.bind(this));
            this.updateImages();
        }

    },
    fixParentTable : function() {
        this.container.ancestors().each( function(parentItem) {
            if (parentItem.tagName.toLowerCase() == 'td') {
                parentItem.style.width = '100%';
            }
            if (parentItem.tagName.toLowerCase() == 'table') {
                parentItem.style.width = '100%';
                throw $break;
            }
        });
    },
    getElement : function(name) {
        return $(this.containerId + '_' + name);
    },
    showUploader : function() {
        this.getElement('add_images_button').hide();
        this.getElement('uploader').show();
    },
    handleUploadComplete : function(file) {
        if (file.error) {
            return;
        }
        var newImage = {};
        newImage.url = file.url;
        newImage.file = file.file;
        newImage.label = '';
        newImage.position = this.getNextPosition();
        newImage.disabled = 0;
        newImage.removed = 0;
        this.images.push(newImage);
        this.container.setHasChanges();
        this.updateImages();
    },
    updateImages : function() {
        this.getElement('save').value = Object.toJSON(this.images);
        $H(this.imageTypes).each(
                function(pair) {
                    this.getFileElement('no_selection',
                            'cell-' + pair.key + ' input').checked = true;
                }.bind(this));
        this.images.each( function(row) {
            if (!$(this.prepareId(row.file))) {
                this.createImageRow(row);
            }
            this.updateVisualisation(row.file);
        }.bind(this));
        this.updateUseDefault(false);
    },
    onChangeRadio: function (evt) {
        var element = Event.element(evt);
        element.setHasChanges();
    },
    createImageRow : function(image) {
        var vars = Object.clone(image);
        vars.id = this.prepareId(image.file);
        var html = this.template.evaluate(vars);
        Element.insert(this.getElement('list'), {
            bottom :html
        });

        $(vars.id).select('input[type="radio"]').each(function(radio) {
            radio.observe('change', this.onChangeRadio);
        }.bind(this));
    },
    prepareId : function(file) {
        if (typeof this.file2id[file] == 'undefined') {
            this.file2id[file] = this.idIncrement++;
        }
        return this.containerId + '-image-' + this.file2id[file];
    },
    getNextPosition : function() {
        var maxPosition = 0;
        this.images.each( function(item) {
            if (parseInt(item.position) > maxPosition) {
                maxPosition = parseInt(item.position);
            }
        });
        return maxPosition + 1;
    },
    updateImage : function(file) {
        var index = this.getIndexByFile(file);
        this.images[index].label = this
                .getFileElement(file, 'cell-label input').value;
        this.images[index].position = this.getFileElement(file,
                'cell-position input').value;
        this.images[index].removed = (this.getFileElement(file,
                'cell-remove input').checked ? 1 : 0);
        this.images[index].disabled = (this.getFileElement(file,
                'cell-disable input').checked ? 1 : 0);
        this.getElement('save').value = Object.toJSON(this.images);
        this.updateState(file);
        this.container.setHasChanges();
    },
    loadImage : function(file) {
        var image = this.getImageByFile(file);
        this.getFileElement(file, 'cell-image img').src = image.url;
        this.getFileElement(file, 'cell-image img').show();
        this.getFileElement(file, 'cell-image .place-holder').hide();
    },
    setProductImages : function(file) {
        $H(this.imageTypes)
                .each(
                        function(pair) {
                            if (this.getFileElement(file,
                                    'cell-' + pair.key + ' input').checked) {
                                this.imagesValues[pair.key] = (file == 'no_selection' ? null
                                        : file);
                            }
                        }.bind(this));

        this.getElement('save_image').value = Object.toJSON($H(this.imagesValues));
    },
    updateVisualisation : function(file) {
        var image = this.getImageByFile(file);
        this.getFileElement(file, 'cell-label input').value = image.label;
        this.getFileElement(file, 'cell-position input').value = image.position;
        this.getFileElement(file, 'cell-remove input').checked = (image.removed == 1);
        this.getFileElement(file, 'cell-disable input').checked = (image.disabled == 1);
        $H(this.imageTypes)
                .each(
                        function(pair) {
                            if (this.imagesValues[pair.key] == file) {
                                this.getFileElement(file,
                                        'cell-' + pair.key + ' input').checked = true;
                            }
                        }.bind(this));
        this.updateState(file);
    },
    updateState : function(file) {
        if (this.getFileElement(file, 'cell-disable input').checked) {
            this.getFileElement(file, 'cell-position input').disabled = true;
        } else {
            this.getFileElement(file, 'cell-position input').disabled = false;
        }
    },
    getFileElement : function(file, element) {
        var selector = '#' + this.prepareId(file) + ' .' + element;
        var elems = $$(selector);
        if (!elems[0]) {
            try {
                console.log(selector);
            } catch (e2) {
                alert(selector);
            }
        }

        return $$('#' + this.prepareId(file) + ' .' + element)[0];
    },
    getImageByFile : function(file) {
        if (this.getIndexByFile(file) === null) {
            return false;
        }

        return this.images[this.getIndexByFile(file)];
    },
    getIndexByFile : function(file) {
        var index;
        this.images.each( function(item, i) {
            if (item.file == file) {
                index = i;
            }
        });
        return index;
    },
    updateUseDefault : function() {
        if (this.getElement('default')) {
            this.getElement('default').select('input').each(
                    function(input) {
                        $(this.containerId).select(
                                '.cell-' + input.value + ' input').each(
                                function(radio) {
                                    radio.disabled = input.checked;
                                });
                    }.bind(this));
        }

        if (arguments.length == 0) {
            this.container.setHasChanges();
        }
    },
    handleUploadProgress : function(file) {

    },
    handleUploadError : function(fileId) {

    }
};

Product.AttributesBridge = {
    tabsObject :false,
    bindTabs2Attributes : {},
    bind : function(tabId, attributesObject) {
        this.bindTabs2Attributes[tabId] = attributesObject;
    },
    getAttributes : function(tabId) {
        return this.bindTabs2Attributes[tabId];
    },
    addAttributeRow : function(data) {
        $H(data).each(function(item) {
            var element = this.getAttributes(item.key).addRow(item.value);
            jQuery(element).trigger('focus');
        }.bind(this));
    }
};

Product.Attributes = Class.create();
Product.Attributes.prototype = {
    config : {},
    containerId :null,
    initialize : function(containerId) {
        this.containerId = containerId;
    },
    setConfig : function(config) {
        this.config = config;
        Product.AttributesBridge.bind(this.getConfig().tab_id, this);
    },
    getConfig : function() {
        return this.config;
    },
    create : function() {
        var win = window.open(this.getConfig().url, 'new_attribute',
                'width=900,height=600,resizable=1,scrollbars=1');
        win.focus();
    },
    addRow : function(html) {
        var attributesContainer = $$('#group_fields' + this.getConfig().group_id + ' .form-list tbody')[0];
        Element.insert(attributesContainer, {
            bottom :html
        });

        var childs = attributesContainer.childElements();
        var element = childs[childs.size() - 1].select('input', 'select',
                'textarea')[0];
        if (element) {
            window.scrollTo(0, Position.cumulativeOffset(element)[1]
                    + element.offsetHeight);
        }
        return element;
    }
};

var onInitDisableFieldsList = [];

function toogleFieldEditMode(toogleIdentifier, fieldContainer) {
    if ($(toogleIdentifier).checked) {
        enableFieldEditMode(fieldContainer);
    } else {
        disableFieldEditMode(fieldContainer);
    }
}

function disableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = true;
    if ($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = true;
    }
}

function enableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = false;
    if ($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = false;
    }
}

function initDisableFields(fieldContainer) {
    onInitDisableFieldsList.push(fieldContainer);
}

function onCompleteDisableInited() {
    onInitDisableFieldsList.each( function(item) {
        disableFieldEditMode(item);
    });
}

function onUrlkeyChanged(urlKey) {
    urlKey = $(urlKey);
    var hidden = urlKey.next('input[type=hidden]');
    var chbx = urlKey.next('input[type=checkbox]');
    var oldValue = chbx.value;
    chbx.disabled = (oldValue == urlKey.value);
    hidden.disabled = chbx.disabled;
}

function onCustomUseParentChanged(element) {
    var useParent = (element.value == 1) ? true : false;
    element.up(2).select('input', 'select', 'textarea').each(function(el){
        if (element.id != el.id) {
            el.disabled = useParent;
        }
    });
    element.up(2).select('img').each(function(el){
        if (useParent) {
            el.hide();
        } else {
            el.show();
        }
    });
}

Event.observe(window, 'load', onCompleteDisableInited);

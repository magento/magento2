/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinymce, widgetTools, jQuery, Base64 */
/* eslint-disable strict */
define([
    'wysiwygAdapter',
    'mage/adminhtml/events',
    'mage/adminhtml/wysiwyg/widget'
], function (wysiwyg, varienGlobalEvents) {
    return function (config) {
        tinymce.create('tinymce.plugins.magentowidget', {

            /**
             * @param {tinymce.Editor} editor - Editor instance that the plugin is initialized in.
             * @param {String} url - Absolute URL to where the plugin is located.
             */
            init: function (editor, url) {
                var self = this;

                this.activePlaceholder = null;

                editor.addCommand('mceMagentowidget', function (img) {
                    if (self.activePlaceholder) {
                        img = self.activePlaceholder;
                    }

                    widgetTools.setActiveSelectedNode(img);
                    widgetTools.openDialog(
                        config['window_url'] + 'widget_target_id/' + editor.getElement().id + '/'
                    );
                });

                // Register Widget plugin button
                editor.addButton('magentowidget', {
                    title: jQuery.mage.__('Insert Widget'),
                    cmd: 'mceMagentowidget',
                    image: url + '/img/icon.png',

                    /**
                     * Fires after the rendering has completed. This ensures the editor will be instantiated
                     */
                    onPostRender: function () {
                        var ctrl = this;

                        // Add a node change handler, selects the button in the UI when a image is selected
                        editor.on('nodeChange', function (e) {
                            var placeholder = e.element;

                            if (self.isWidgetPlaceholderSelected(placeholder)) {
                                widgetTools.setEditMode(true);
                                ctrl.active(true);
                            } else {
                                widgetTools.setEditMode(false);
                                ctrl.active(false);
                            }
                        });
                    }
                });

                // Add a widget placeholder image double click callback
                editor.on('dblClick', function (e) {
                    var placeholder = e.target;

                    if (self.isWidgetPlaceholderSelected(placeholder)) {
                        widgetTools.setEditMode(true);
                        this.execCommand('mceMagentowidget', null);
                    }
                });

                /**
                 * Attach event handler for when wysiwyg editor is about to encode its content
                 */
                varienGlobalEvents.attachEventHandler('wysiwygEncodeContent', function (content) {
                    content = self.encodeWidgets(self.decodeWidgets(content));
                    content = self.removeDuplicateAncestorWidgetSpanElement(content);

                    return content;
                });

                /**
                 * Attach event handler for when wysiwyg editor is about to decode its content
                 */
                varienGlobalEvents.attachEventHandler('wysiwygDecodeContent', function (content) {
                    content = self.decodeWidgets(content);

                    return content;
                });

                /**
                 * Attach event handler for when popups associated with wysiwyg are about to be closed
                 */
                varienGlobalEvents.attachEventHandler('wysiwygClosePopups', function () {
                    wysiwyg.closeEditorPopup('widget_window' + wysiwyg.getId());
                });
            },

            /**
             * @param {Object} placeholder - Contains the selected node
             * @returns {Boolean}
             */
            isWidgetPlaceholderSelected: function (placeholder) {
                var isSelected = false;

                if (placeholder.nodeName &&
                    (placeholder.nodeName === 'SPAN' || placeholder.nodeName === 'IMG') &&
                    placeholder.className && placeholder.className.indexOf('magento-widget') !== -1
                ) {
                    this.activePlaceholder = placeholder;
                    isSelected = true;
                } else {
                    this.activePlaceholder = null;
                }

                return isSelected;
            },

            /**
             * Convert {{widget}} style syntax to image placeholder HTML
             * @param {String} content
             * @return {*}
             */
            encodeWidgets: function (content) {
                return content.gsub(/\{\{widget(.*?)\}\}/i, function (match) {
                    var attributes = wysiwyg.parseAttributesString(match[1]),
                        imageSrc,
                        imageHtml = '';

                    if (attributes.type) {
                        attributes.type = attributes.type.replace(/\\\\/g, '\\');
                        imageSrc = config.placeholders[attributes.type];

                        if (imageSrc) {
                            imageHtml += '<span class="magento-placeholder magento-widget mceNonEditable" ' +
                                'contenteditable="false">';
                        } else {
                            imageSrc = config['error_image_url'];
                            imageHtml += '<span ' +
                                'class="magento-placeholder magento-placeholder-error magento-widget mceNonEditable" ' +
                                'contenteditable="false">';
                        }

                        imageHtml += '<img';
                        imageHtml += ' id="' + Base64.idEncode(match[0]) + '"';
                        imageHtml += ' src="' + imageSrc + '"';
                        imageHtml += ' />';

                        if (config.types[attributes.type]) {
                            imageHtml += config.types[attributes.type];
                        }

                        imageHtml += '</span>';

                        return imageHtml;
                    }
                });
            },

            /**
             * Convert image placeholder HTML to {{widget}} style syntax
             * @param {String} content
             * @return {*}
             */
            decodeWidgets: function (content) {
                return content.gsub(
                    /(<span class="[^"]*magento-widget[^"]*"[^>]*>)?<img([^>]+id="[^>]+)>(([^>]*)<\/span>)?/i,
                    function (match) {
                        var attributes = wysiwyg.parseAttributesString(match[2]),
                            widgetCode,
                            result = match[0];

                        if (attributes.id) {
                            try {
                                widgetCode = Base64.idDecode(attributes.id);
                            } catch (e) {
                                // Ignore and continue.
                            }

                            if (widgetCode && widgetCode.indexOf('{{widget') !== -1) {
                                result = widgetCode;
                            }
                        }

                        return result;
                    }
                );
            },

            /**
             * Tinymce has strange behavior with html and this removes one of its side-effects
             * @param {String} content
             * @return {String}
             */
            removeDuplicateAncestorWidgetSpanElement: function (content) {
                var parser, doc, returnval = '';

                if (!window.DOMParser) {
                    return content;
                }

                parser = new DOMParser();
                doc = parser.parseFromString(content.replace(/&quot;/g, '&amp;quot;'), 'text/html');

                [].forEach.call(doc.querySelectorAll('.magento-widget'), function (widgetEl) {
                    var widgetChildEl = widgetEl.querySelector('.magento-widget');

                    if (!widgetChildEl) {
                        return;
                    }

                    [].forEach.call(widgetEl.childNodes, function (el) {
                        widgetEl.parentNode.insertBefore(el, widgetEl);
                    });

                    widgetEl.parentNode.removeChild(widgetEl);
                });

                returnval += doc.head ? doc.head.innerHTML.replace(/&amp;quot;/g, '&quot;') : '';
                returnval += doc.body ? doc.body.innerHTML.replace(/&amp;quot;/g, '&quot;') : '';

                return returnval ? returnval : content;
            },

            /**
             * @return {Object}
             */
            getInfo: function () {
                return {
                    longname: 'Magento Widget Manager Plugin',
                    author: 'Magento Core Team',
                    authorurl: 'http://magentocommerce.com',
                    infourl: 'http://magentocommerce.com',
                    version: '1.0'
                };
            }
        });

        // Register plugin
        tinymce.PluginManager.add('magentowidget', tinymce.plugins.magentowidget);
    };
});

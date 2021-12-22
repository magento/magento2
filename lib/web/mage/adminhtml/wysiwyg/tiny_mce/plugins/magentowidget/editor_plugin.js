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
             */
            init: function (editor) {
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
                editor.ui.registry.addIcon(
                    'magentowidget',
                    '<svg width="24" height="24" viewBox="0 0 32.000000 32.000000" ' +
                    'preserveAspectRatio="xMidYMid meet"> <g transform="translate(0.000000,32.000000) ' +
                    'scale(0.100000,-0.100000)" fill="#000000" stroke="none"> <path d="M130 290 c0 -5 13 -10 30 ' +
                    '-10 22 0 28 -4 24 -15 -5 -11 2 -15 26 -15 21 0 30 -4 28 -12 -7 -20 -40 -22 -50 -4 -5 9 -14 16 ' +
                    '-20 16 -6 0 -19 7 -28 15 -9 8 -25 12 -38 8 -33 -8 -27 -26 8 -21 34 5 40 -6 12 -21 -14 -7 -25 -6 ' +
                    '-40 5 -12 8 -23 10 -27 5 -5 -8 88 -71 105 -71 3 0 29 14 58 31 l53 30 -23 18 c-13 10 -31 20 -40 ' +
                    '24 -10 3 -18 11 -18 17 0 5 -13 10 -30 10 -16 0 -30 -4 -30 -10z m58 -82 c-3 -7 -15 -13 -28 -13 ' +
                    '-13 0 -25 6 -27 13 -3 8 6 12 27 12 21 0 30 -4 28 -12z"/> <path d="M30 151 l0 -60 61 -36 c33 ' +
                    '-19 64 -35 69 -35 5 0 36 16 69 35 l61 36 0 60 0 61 -65 -37 -65 -36 -65 36 -65 37 0 -61z"/> </g>' +
                    '</svg>'
                );
                editor.ui.registry.addToggleButton('magentowidget', {
                    icon: 'magentowidget',
                    tooltip: jQuery.mage.__('Insert Widget'),

                    /**
                     * execute openVariablesSlideout for onAction callback
                     */
                    onAction: function () {
                        editor.execCommand('mceMagentowidget');
                    },

                    /**
                     * Add a node change handler, selects the button in the UI when a image is selected
                     * @param {ToolbarToggleButtonInstanceApi} api
                     */
                    onSetup: function (api) {
                        /**
                         * NodeChange handler
                         */
                        editor.on('NodeChange', function (e) {
                            var placeholder = e.element;

                            if (self.isWidgetPlaceholderSelected(placeholder)) {
                                widgetTools.setEditMode(true);
                                api.setActive(true);
                            } else {
                                widgetTools.setEditMode(false);
                                api.setActive(false);
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
                return content.gsub(/\{\{widget([\S\s]*?)\}\}/i, function (match) {
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

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinymce, MagentovariablePlugin, varienGlobalEvents, Base64 */
/* eslint-disable strict */
define([
    'Magento_Variable/js/config-directive-generator',
    'Magento_Variable/js/custom-directive-generator',
    'wysiwygAdapter',
    'jquery',
    'mage/adminhtml/tools'
], function (configDirectiveGenerator, customDirectiveGenerator, wysiwyg, jQuery) {
    return function (config) {
        tinymce.create('tinymce.plugins.magentovariable', {

            /**
             * Initialize editor plugin.
             *
             * @param {tinymce.editor} editor - Editor instance that the plugin is initialized in.
             * @param {String} url - Absolute URL to where the plugin is located.
             */
            init: function (editor, url) {
                var self = this;

                /**
                 * Add new command to open variables selector slideout.
                 */
                editor.addCommand('openVariablesSlideout', function (commandConfig) {
                    var selectedElement;

                    if (commandConfig) {
                        selectedElement = commandConfig.selectedElement;
                    } else {
                        selectedElement = tinymce.activeEditor.selection.getNode();
                    }
                    MagentovariablePlugin.setEditor(editor);
                    MagentovariablePlugin.loadChooser(
                        config.url,
                        wysiwyg.getId(),
                        selectedElement
                    );
                });

                /**
                 * Add button to the editor toolbar.
                 */
                editor.addButton('magentovariable', {
                    title: jQuery.mage.__('Insert Variable'),
                    tooltip: jQuery.mage.__('Insert Variable'),
                    cmd: 'openVariablesSlideout',
                    image: url + '/img/icon.png',

                    /**
                     * Highlight or dismiss Insert Variable button when variable is selected or deselected.
                     */
                    onPostRender: function () {
                        var _this = this,

                        /**
                         * Toggle active state of Insert Variable button.
                         *
                         * @param {Object} e
                         */
                        toggleVariableButton = function (e) {
                            _this.active(false);

                            if (jQuery(e.target).hasClass('magento-variable')) {
                                _this.active(true);
                            }
                        };

                        editor.on('click', toggleVariableButton);
                        editor.on('change', toggleVariableButton);
                    }
                });

                /**
                 * Double click handler on the editor to handle dbl click on variable placeholder.
                 */
                editor.on('dblclick', function (evt) {
                    if (jQuery(evt.target).hasClass('magento-variable')) {
                        editor.selection.collapse(false);
                        editor.execCommand('openVariablesSlideout', {
                            ui: true,
                            selectedElement: evt.target
                        });
                    }
                });

                /**
                 * Attach event handler for when wysiwyg editor is about to encode its content
                 */
                varienGlobalEvents.attachEventHandler('wysiwygEncodeContent', function (content) {
                    content = self.encodeVariables(content);

                    return content;
                });

                /**
                 * Attach event handler for when wysiwyg editor is about to decode its content
                 */
                varienGlobalEvents.attachEventHandler('wysiwygDecodeContent', function (content) {
                    content = self.decodeVariables(content);

                    return content;
                });
            },

            /**
             * Encode variables in content
             *
             * @param {String} content
             * @returns {*}
             */
            encodeVariables: function (content) {
                content = content.gsub(/\{\{config path=\"([^\"]+)\"\}\}/i, function (match) {
                    var path = match[1],
                        magentoVariables,
                        imageHtml;

                    magentoVariables = JSON.parse(config.placeholders);

                    if (magentoVariables[match[1]] && magentoVariables[match[1]]['variable_type'] === 'default') {
                        imageHtml = '<span id="%id" class="magento-variable magento-placeholder mceNonEditable">' +
                            '%s</span>';
                        imageHtml = imageHtml.replace('%s', magentoVariables[match[1]]['variable_name']);
                    } else {
                        imageHtml = '<span id="%id" class="' +
                            'magento-variable magento-placeholder magento-placeholder-error ' +
                            'mceNonEditable' +
                            '">' +
                            'Not found' +
                            '</span>';
                    }

                    return imageHtml.replace('%id', Base64.idEncode(path));
                });

                content = content.gsub(/\{\{customVar code=([^\}\"]+)\}\}/i, function (match) {
                    var path = match[1],
                        magentoVariables,
                        imageHtml;

                    magentoVariables = JSON.parse(config.placeholders);

                    if (magentoVariables[match[1]] && magentoVariables[match[1]]['variable_type'] === 'custom') {
                        imageHtml = '<span id="%id" class="magento-variable magento-custom-var magento-placeholder ' +
                            'mceNonEditable">%s</span>';
                        imageHtml = imageHtml.replace('%s', magentoVariables[match[1]]['variable_name']);
                    } else {
                        imageHtml = '<span id="%id" class="' +
                            'magento-variable magento-custom-var magento-placeholder ' +
                            'magento-placeholder-error mceNonEditable' +
                            '">' +
                            match[1] +
                            '</span>';
                    }

                    return imageHtml.replace('%id', Base64.idEncode(path));
                });

                return content;
            },

            /**
             * Decode variables in content.
             *
             * @param {String} content
             * @returns {String}
             */
            decodeVariables: function (content) {
                var doc = (new DOMParser()).parseFromString(content.replace(/&quot;/g, '&amp;quot;'), 'text/html');

                [].forEach.call(doc.querySelectorAll('span.magento-variable'), function (el) {
                    var $el = jQuery(el);

                    if ($el.hasClass('magento-custom-var')) {
                        $el.replaceWith(
                            customDirectiveGenerator.processConfig(
                                Base64.idDecode(
                                    $el.attr('id')
                                )
                            )
                        );
                    } else {
                        $el.replaceWith(
                            configDirectiveGenerator.processConfig(
                                Base64.idDecode(
                                    $el.attr('id')
                                )
                            )
                        );
                    }
                });

                return doc.body ? doc.body.innerHTML.replace(/&amp;quot;/g, '&quot;') : content;
            },

            /**
             * @return {Object}
             */
            getInfo: function () {
                return {
                    longname: 'Magento Variable Manager Plugin',
                    author: 'Magento Core Team',
                    authorurl: 'http://magentocommerce.com',
                    infourl: 'http://magentocommerce.com',
                    version: '1.0'
                };
            }
        });

        /**
         * Register plugin
         */
        tinymce.PluginManager.add('magentovariable', tinymce.plugins.magentovariable);
    };
});

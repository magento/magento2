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
        var self = null,

            magentovariable = function (editor) {
                this.widget = {
                    /**
                     * Encode variables in content
                     *
                     * @param {String} content
                     * @returns {*}
                     */
                    encodeVariables: function (content) {
                        content = content.gsub(/\{\{config path="([^"]+)"}}/i, function (match) {
                            var path = match[1],
                                magentoVariables,
                                imageHtml;

                            magentoVariables = JSON.parse(config.placeholders);

                            if (magentoVariables[match[1]]
                                && magentoVariables[match[1]]['variable_type'] === 'default') {
                                imageHtml = '<span id="%id" ' +
                                    'class="magento-variable magento-placeholder mceNonEditable">' +
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

                        content = content.gsub(/\{\{customVar code=([^}"]+)}}/i, function (match) {
                            var path = match[1],
                                magentoVariables,
                                imageHtml;

                            magentoVariables = JSON.parse(config.placeholders);

                            if (magentoVariables[match[1]]
                                && magentoVariables[match[1]]['variable_type'] === 'custom') {
                                imageHtml = '<span id="%id" class="magento-variable magento-custom-var ' +
                                    'magento-placeholder mceNonEditable">%s</span>';
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
                        var doc = new DOMParser().parseFromString(content.replace(/&quot;/g, '&amp;quot;'),
                                'text/html'),
                            returnval = '';

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

                        returnval += doc.head ? doc.head.innerHTML.replace(/&amp;quot;/g, '&quot;') : '';
                        returnval += doc.body ? doc.body.innerHTML.replace(/&amp;quot;/g, '&quot;') : '';

                        return returnval ? returnval : content;
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
                };

                self = this.widget;

                this.activePlaceholder = null;

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
                editor.ui.registry.addIcon(
                    'magentovariable',
                    '<svg width="24" height="24" viewBox="0 0 32.000000 32.000000" ' +
                    'preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,32.000000) ' +
                    'scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M68 250 c-56 -44 -75 -136 -37 ' +
                    '-184 27 -34 42 -33 23 2 -26 50 -9 129 38 179 26 28 10 30 -24 3z"/><path d="M266 253 c5 -10 9 ' +
                    '-41 9 -70 0 -42 -6 -60 -32 -97 -36 -51 -35 -56 7 -26 54 39 78 139 44 188 -18 26 -40 30 -28 5z"/>' +
                    '<path d="M128 223 c-15 -4 -15 -6 0 -33 16 -28 16 -30 -11 -58 -30 -31 -34 -42 -13 -42 8 0 17 11 ' +
                    '20 25 4 14 11 25 16 25 5 0 12 -11 16 -25 6 -25 37 -35 49 -15 3 5 2 10 -3 10 -23 0 -20 44 5 76 ' +
                    '25 34 25 34 4 34 -12 0 -20 -4 -17  -8 2 -4 0 -14 -5 -22 -7 -10 -12 -11 -15 -4 -10 25 -30 40 ' +
                    '-46 37z"/></g>' +
                    '</svg>'
                );
                editor.ui.registry.addToggleButton('magentovariable', {
                    icon: 'magentovariable',
                    tooltip: jQuery.mage.__('Insert Variable'),

                    /**
                     * execute openVariablesSlideout for onAction callback
                     */
                    onAction: function () {
                        editor.execCommand('openVariablesSlideout');
                    },

                    /**
                     * Highlight or dismiss Insert Variable button when variable is selected or deselected.
                     */
                    onSetup: function (api) {
                        /**
                         * Toggle active state of Insert Variable button.
                         *
                         * @param {Object} e
                         */
                        var toggleVariableButton = function (e) {
                            api.setActive(false);

                            if (jQuery(e.target).hasClass('magento-variable')) {
                                api.setActive(true);
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
            };

        /**
         * Register plugin
         */
        tinymce.PluginManager.add('magentovariable', magentovariable);
    };
});

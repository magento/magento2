/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinymce, MagentovariablePlugin, varienGlobalEvents, Base64 */
/* eslint-disable strict */
tinymce.create('tinymce.plugins.magentovariable', {

    /**
     * Initialize editor plugin.
     *
     * @param {tinymce.editor} editor - Editor instance that the plugin is initialized in.
     * @param {String} url - Absolute URL to where the plugin is located.
     */
    init: function (editor, url) {
        var self = this;

        require([
            'Magento_Variable/js/config-directive-generator',
            'Magento_Variable/js/custom-directive-generator'
        ], function (configDirectiveGenerator, customDirectiveGenerator) {
            self.configDirectiveGenerator = configDirectiveGenerator;
            self.customDirectiveGenerator = customDirectiveGenerator;
        });

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
                self.constructor.config.url,
                self.constructor.adapter.id,
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

            magentoVariables = JSON.parse(this.constructor.config.placeholders);

            if (magentoVariables[match[1]] && magentoVariables[match[1]]['variable_type'] === 'default') {
                imageHtml = '<span id="%id" class="magento-variable magento-placeholder mceNonEditable">%s</span>';
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
        }.bind(this));

        content = content.gsub(/\{\{customVar code=([^\}\"]+)\}\}/i, function (match) {
            var path = match[1],
                magentoVariables,
                imageHtml;

            magentoVariables = JSON.parse(this.constructor.config.placeholders);

            if (magentoVariables[match[1]] && magentoVariables[match[1]]['variable_type'] === 'custom') {
                imageHtml = '<span id="%id" class="magento-variable magento-custom-var magento-placeholder ' +
                    'mceNonEditable">%s</span>';
                imageHtml = imageHtml.replace('%s', magentoVariables[match[1]]['variable_name']);
            } else {
                imageHtml = '<span id="%id" class="' +
                    'magento-variable magento-custom-var magento-placeholder ' +
                    'magento-placeholder-error mceNonEditable' +
                    '">' +
                    match[1]  +
                    '</span>';
            }

            return imageHtml.replace('%id', Base64.idEncode(path));
        }.bind(this));

        return content;
    },

    /**
     * Decode variables in content.
     *
     * @param {String} content
     * @returns {*}
     */
    decodeVariables: function (content) {
        var i, el, spans, element = document.createElement('elem');

        element.innerHTML = content;
        spans = element.querySelectorAll('span.magento-variable');

        for (i = 0; i < spans.length; i++) {
            el = spans[i];

            if (el.hasClassName('magento-custom-var')) {
                $(el).replaceWith(
                    this.customDirectiveGenerator.processConfig(
                        Base64.idDecode(
                            $(el).getAttribute('id')
                        )
                    )
                );
            } else {
                $(el).replaceWith(
                    this.configDirectiveGenerator.processConfig(
                        Base64.idDecode(
                            $(el).getAttribute('id')
                        )
                    )
                );
            }
        }

        return element.innerHTML;
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

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinymce, MagentovariablePlugin */
/* eslint-disable strict */

(function () {
    tinymce.create('tinymce.plugins.magentoVariablesPlugin', {

        /**
         * Initialize editor plugin.
         *
         * @param {tinymce.editor} editor - Editor instance that the plugin is initialized in.
         * @param {String} url - Absolute URL to where the plugin is located.
         */
        init: function (editor, url) {

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
                    editor.settings.magentoPluginsOptions.magentovariable.url,
                    this.settings.adapter.id,
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
                    var _this = this;

                    editor.on('click', function (e) {
                        _this.active(false);

                        if (jQuery(e.target).hasClass('magento-variable')) {
                            _this.active(true);
                        }
                    });
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
    tinymce.PluginManager.add('magentovariable', tinymce.plugins.magentoVariablesPlugin);
})();

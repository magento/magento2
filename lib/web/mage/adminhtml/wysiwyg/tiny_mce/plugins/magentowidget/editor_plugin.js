/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinymce, widgetTools */
/* eslint-disable strict */
(function () {

    tinymce.create('tinymce.plugins.MagentoWidget', {
        /**
         * @param {tinymce.Editor} editor - Editor instance that the plugin is initialized in.
         * @param {String} url - Absolute URL to where the plugin is located.
         */
        init: function (editor, url) {
            var activePlaceholder;

            /**
             *
             * @param {Object} placeholder - Contains the selected node
             * @returns {Boolean}
             */
            function isWidgetPlaceholderSelected(placeholder) {
                var isSelected = false,
                    spanElement;

                activePlaceholder = undefined;

                //If the user clicks on the image we need to set the parent as the selected node
                if (placeholder.nodeName &&
                    placeholder.nodeName === 'IMG' &&
                    placeholder.parentNode &&
                    placeholder.parentNode.nodeName === 'SPAN')
                {
                    placeholder = placeholder.parentNode;
                }

                if (placeholder.nodeName &&
                    placeholder.nodeName === 'SPAN' &&
                    placeholder.className &&
                    placeholder.className.indexOf('magento-widget') !== -1) {
                    activePlaceholder = placeholder;
                    isSelected = true;
                }

                return isSelected;
            }

            editor.addCommand('mceMagentowidget', function (img) {
                if (activePlaceholder) {
                    img = activePlaceholder;
                }
                widgetTools.setActiveSelectedNode(img);
                widgetTools.openDialog(
                    editor.settings['magentowidget_url'] + 'widget_target_id/' + editor.getElement().id + '/'
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

                        widgetTools.setEditMode(false);
                        ctrl.active(false);

                        if (isWidgetPlaceholderSelected(placeholder)) {
                            widgetTools.setEditMode(true);
                            ctrl.active(true);
                        }
                    });
                }
            });

            // Add a widget placeholder image double click callback
            editor.on('dblClick', function (e) {
                var placeholder = e.target;

                if (isWidgetPlaceholderSelected(placeholder)) {
                    widgetTools.setEditMode(true);
                    this.execCommand('mceMagentowidget', null);
                }
            });
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
    tinymce.PluginManager.add('magentowidget', tinymce.plugins.MagentoWidget);
})();

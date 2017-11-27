/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinyMCE, tinymce, widgetTools, Base64 */
/* eslint-disable strict */

(function () {

    tinymce.create('tinymce.plugins.MagentoWidget', {
        /**
         * @param {wysiwyg.Editor} editor - Editor instance that the plugin is initialized in.
         * @param {String} url - Absolute URL to where the plugin is located.
         */
        init: function (editor, url) {
            var activePlaceholder;

            editor.addCommand('mceMagentowidget', function (img) {
                if (!img && activePlaceholder)                {
                    img = getImage(activePlaceholder);
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
                onPostRender: function () {
                    ctrl = this;

                    // Add a node change handler, selects the button in the UI when a image is selected
                    editor.on('nodeChange', function (e) {
                        var placeholder = e.element;

                        ctrl.active(false);
                        if (isWidgetPlaceholderSelected(placeholder)) {
                            ctrl.active(true);
                        }
                    });
                }
            });

            // Add a widget placeholder image double click callback
            editor.on('dblClick', function (e) {
                var placeholder = e.target;
                if (isWidgetPlaceholderSelected(placeholder)) {
                    this.execCommand('mceMagentowidget', getImage(placeholder));
                }
            });

            function isWidgetPlaceholderSelected(placeholder)
            {
                var isSelected = false;

                if (placeholder.nodeName && (placeholder.nodeName === 'SPAN' || placeholder.nodeName === 'IMG')
                    && placeholder.className && placeholder.className.indexOf("magento-widget") !== -1) {
                    activePlaceholder = placeholder;
                    isSelected = true;
                }
                else {
                    activePlaceholder = undefined;
                }
                return isSelected;
            }

            function getImage(el) {
                var widgetCode,
                    $el = jQuery(el).closest('.magento-widget'),
                    img,
                    isWidgetPlaceholder = $el.length
                ;

                if (!isWidgetPlaceholder) {
                    return;
                }

                img = $el.find('img').get(0);

                widgetCode = Base64.idDecode(img.id);

                if (widgetCode.indexOf('{{widget') !== -1) {
                    return img;
                }
                return;
            }
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

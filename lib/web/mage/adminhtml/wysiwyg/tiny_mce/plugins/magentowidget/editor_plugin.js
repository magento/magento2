/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinyMCE, tinymce, widgetTools, Base64 */
/* eslint-disable strict */

(function () {

    tinymce.create('tinymce.plugins.MagentoWidget', {
        /**
         * @param {tinymce.Editor} ed - Editor instance that the plugin is initialized in.
         * @param {String} url - Absolute URL to where the plugin is located.
         */
        init: function (ed, url) {
            var ctrl;

            ed.addCommand('mceMagentowidget', function (img) {
                var id = ed.getElement().id;

                widgetTools.setActiveSelectedNode(img);

                widgetTools.openDialog(
                    ed.settings['magentowidget_url'] + 'widget_target_id/' + id + '/'
                );
            });

            // Register Widget plugin button
            ed.addButton('magentowidget', {
                title: jQuery.mage.__('Insert Widget'),
                cmd: 'mceMagentowidget',
                image: url + '/img/icon.png',
                onPostRender: function () {
                    ctrl = this;

                    // Add a node change handler, selects the button in the UI when a image is selected
                    ed.on('nodeChange', function (e) {
                        var el = e.element,
                            widgetCode;

                        ctrl.active(false);

                        if (el.id && el.nodeName === 'IMG') {
                            widgetCode = Base64.idDecode(el.id);

                            if (widgetCode.indexOf('{{widget') !== -1) {
                                ctrl.active(true);
                            }
                        }
                    });
                }
            });

            function executeWidgetSlideoutIfElHasWidgetCode (el) {
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
                    this.execCommand('mceMagentowidget', img);
                }
            }

            // Add a widget placeholder image double click callback
            ed.on('dblClick', function (e) {
                executeWidgetSlideoutIfElHasWidgetCode.call(this, e.target);
            });

            // Add an on enter press callback
            ed.on('keyup', function (e) {
                var key = e.which || e.keyCode || 0,
                    isEnterKey = key === 69;

                if (!isEnterKey) {
                    return;
                }

                executeWidgetSlideoutIfElHasWidgetCode.call(this, e.target);
            });
        },

        /**
         * @return {Object}
         */
        getInfo: function () {
            return {
                longname: 'Magento Widget Manager Plugin for TinyMCE 4.x',
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

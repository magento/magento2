/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
tinyMCE.addI18n({en:{
    magentowidget:
    {
        insert_widget : "Insert Widget"
    }
}});

/*
    TODO: Apply JStrim to reduce file size
*/

(function() {
    tinymce.create('tinymce.plugins.MagentowidgetPlugin', {
        /**
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addCommand('mceMagentowidget', function() {
                widgetTools.openDialog(ed.settings.magentowidget_url + 'widget_target_id/' + ed.getElement().id + '/');
            });

            // Register Widget plugin button
            ed.addButton('magentowidget', {
                title : 'magentowidget.insert_widget',
                cmd : 'mceMagentowidget',
                image : url + '/img/icon.gif'
            });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add(function(ed, cm, n) {
                cm.setActive('magentowidget', false);
                if (n.id && n.nodeName == 'IMG') {
                    var widgetCode = Base64.idDecode(n.id);
                    if (widgetCode.indexOf('{{widget') != -1) {
                        cm.setActive('magentowidget', true);
                    }
                }
            });

            // Add a widget placeholder image double click callback
            ed.onDblClick.add(function(ed, e) {
                var n = e.target;
                if (n.id && n.nodeName == 'IMG') {
                    var widgetCode = Base64.idDecode(n.id);
                    if (widgetCode.indexOf('{{widget') != -1) {
                        ed.execCommand('mceMagentowidget');
                    }
                }
            });
        },

        getInfo : function() {
            return {
                longname : 'Magento Widget Manager Plugin for TinyMCE 3.x',
                author : 'Magento Core Team',
                authorurl : 'http://magentocommerce.com',
                infourl : 'http://magentocommerce.com',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('magentowidget', tinymce.plugins.MagentowidgetPlugin);
})();

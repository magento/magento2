/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinyMCE, tinymceDeprecated, MagentovariablePlugin */
/* eslint-disable strict */
//

(function () {

    tinymce.PluginManager.add('magentovariable', function (ed, url) {
            ed.addCommand('openVariablesSlideout', function (ui, variableCode) {
                var pluginSettings = ed.settings.magentoPluginsOptions.magentovariable;

        MagentovariablePlugin.setEditor(ed);
        ed.settings.adapter.turnOff();
        MagentovariablePlugin.loadChooser(pluginSettings.url, this.settings.adapter.id, variableCode);
    }),

    ed.addButton('magentovariable', {
        title: 'magentovariable.insert_variable',
        cmd: 'mceMagentovariable',
        image: url + '/img/icon.gif'
    });

    ed.on('dblclick', function (evt) {
        var $el = jQuery(evt.target),
            isVariable = $el.hasClass('magento-variable'),
            variableCode;

        if (isVariable) {
            variableCode = $el.attr('id');
            $el.remove();
            ed.execCommand('openVariablesSlideout', true, Base64.idDecode(variableCode));
        }
    });

    return {
        getMetadata: function () {
            return {
                longname: 'Magento Variable Manager Plugin for TinyMCE 4.x',
                author: 'Magento Core Team',
                authorurl: 'http://magentocommerce.com',
                infourl: 'http://magentocommerce.com',
                version: '1.0'
            };
        }
    };
});

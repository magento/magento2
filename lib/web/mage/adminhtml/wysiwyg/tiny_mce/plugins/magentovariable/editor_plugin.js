/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinyMCE, tinymceDeprecated, MagentovariablePlugin */
/* eslint-disable strict */
//

(function () {

    tinymce.PluginManager.add('magentovariable', function (ed, url) {
            ed.addCommand('openVariablesSlideout', function (commandConfig) {
        var ui, variableCode, selectedElement;
        if (commandConfig) {
            ui = commandConfig.ui;
            variableCode = commandConfig.variableCode;
            selectedElement = commandConfig.selectedElement;
        }
                var pluginSettings = ed.settings.magentoPluginsOptions.magentovariable;
        MagentovariablePlugin.setEditor(ed);
        MagentovariablePlugin.loadChooser(pluginSettings.url, this.settings.adapter.id, variableCode, selectedElement);
    }),

    ed.addButton('magentovariable', {
        title: 'magentovariable.insert_variable',
        cmd: 'openVariablesSlideout',
        image: url + '/img/icon.gif'
    });

    ed.on('dblclick', function (evt) {

        var $el = jQuery(evt.target),
            isVariable = $el.hasClass('magento-variable'),
            isCustomVar = $el.hasClass('magento-custom-var'),
            variableCode,
            variableType;

        if (isVariable) {
            variableCode = Base64.idDecode($el.attr('id'));
            variableType = isCustomVar ? 'custom' : 'default';
            var node = ed.dom.select('#' + $el.attr('id'));
            ed.selection.collapse(false);
            // node[0].remove();
            // $el.replaceWith('');
            ed.execCommand('openVariablesSlideout', {
                ui: true,
                variableCode: variableType + ':' + variableCode,
                selectedElement: $el
            });
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

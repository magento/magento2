/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    shim: {
        'tinymceDeprecated': { 'exports': 'tinymce' },
        'tinymce4': { 'exports': 'tinymce' }
    },
    paths: {
        'ui/template': 'Magento_Ui/templates',
        'tinymceDeprecated': 'Magento_Tinymce3/tiny_mce/tiny_mce_src',
        'tinymce4': 'tiny_mce_4/tinymce.min',
        'wysiwygAdapter': 'mage/adminhtml/wysiwyg/tiny_mce/tinymce4Adapter'
    },
    map: {
        '*': {
            uiElement:      'Magento_Ui/js/lib/core/element/element',
            uiCollection:   'Magento_Ui/js/lib/core/collection',
            uiComponent:    'Magento_Ui/js/lib/core/collection',
            uiClass:        'Magento_Ui/js/lib/core/class',
            uiEvents:       'Magento_Ui/js/lib/core/events',
            uiRegistry:     'Magento_Ui/js/lib/registry/registry',
            consoleLogger:  'Magento_Ui/js/lib/logger/console-logger',
            uiLayout:       'Magento_Ui/js/core/renderer/layout',
            buttonAdapter:  'Magento_Ui/js/form/button-adapter'
        }
    }
};

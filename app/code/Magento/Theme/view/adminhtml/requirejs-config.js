/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    'shim': {
        'extjs/ext-tree': [
            'prototype'
        ],
        'extjs/ext-tree-checkbox': [
            'extjs/ext-tree',
            'extjs/defaults'
        ],
        'jquery/editableMultiselect/js/jquery.editable': [
            'jquery'
        ]
    },
    'bundles': {
        'js/theme': [
            'globalNavigation',
            'globalSearch',
            'modalPopup',
            'useDefault',
            'loadingPopup',
            'collapsable'
        ]
    },
    'map': {
        '*': {
            'translateInline':      'mage/translate-inline',
            'form':                 'mage/backend/form',
            'button':               'mage/backend/button',
            'accordion':            'mage/accordion',
            'actionLink':           'mage/backend/action-link',
            'validation':           'mage/backend/validation',
            'notification':         'mage/backend/notification',
            'loader':               'mage/loader_old',
            'loaderAjax':           'mage/loader_old',
            'floatingHeader':       'mage/backend/floating-header',
            'suggest':              'mage/backend/suggest',
            'mediabrowser':         'jquery/jstree/jquery.jstree',
            'tabs':                 'mage/backend/tabs',
            'treeSuggest':          'mage/backend/tree-suggest',
            'calendar':             'mage/calendar',
            'dropdown':             'mage/dropdown_old',
            'collapsible':          'mage/collapsible',
            'menu':                 'mage/backend/menu',
            'jstree':               'jquery/jstree/jquery.jstree',
            'details':              'jquery/jquery.details'
        }
    },
    'deps': [
        'js/theme',
        'mage/backend/bootstrap',
        'mage/adminhtml/globals'
    ],
    'paths': {
        'jquery/ui': 'jquery/jquery-ui-1.9.2'
    }
};

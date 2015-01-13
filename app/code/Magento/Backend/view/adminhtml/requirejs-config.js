/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            editTrigger:        'mage/edit-trigger',
            translateInline:    'mage/translate-inline',
            form:               'mage/backend/form',
            button:             'mage/backend/button',
            accordion:          'mage/accordion',
            actionLink:         'mage/backend/action-link',
            validation:         'mage/backend/validation',
            notification:       'mage/backend/notification',
            loader:             'mage/loader_old',
            loaderAjax:         'mage/loader_old',
            floatingHeader:     'mage/backend/floating-header',
            suggest:            'mage/backend/suggest',
            mediabrowser:       'jquery/jstree/jquery.jstree',
            tabs:               'mage/backend/tabs',
            treeSuggest:        'mage/backend/tree-suggest',
            calendar:           'mage/calendar',
            dropdown:           'mage/dropdown_old',
            collapsable:        'js/theme',
            collapsible:        'mage/collapsible',
            menu:               'mage/backend/menu',
            jstree:             'jquery/jstree/jquery.jstree'
        }
    },
    deps: [
        "js/theme",
        'jquery/jquery-migrate',
        "mage/dropdown_old",
        "mage/backend/bootstrap"
    ],
    paths: {
        "jquery/ui": "jquery/jquery-ui-1.9.2"
    }
};

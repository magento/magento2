/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var config = {
    map: {
        '*': {
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
        "mage/dropdown_old",
        "mage/backend/bootstrap"
    ],
    paths: {
        "jquery/ui": "jquery/jquery-ui-1.9.2"
    }
};
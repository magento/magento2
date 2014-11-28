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
            cookieBlock:            'Magento_Theme/js/notices',
            rowBuilder:             'Magento_Theme/js/row-builder',
            toggleAdvanced:         'mage/toggle',
            translateInline:        'mage/translate-inline',
            sticky:                 'mage/sticky',
            tabs:                   'mage/tabs',
            zoom:                   'mage/zoom',
            gallery:                'mage/gallery',
            galleryFullScreen:      'mage/gallery-fullscreen',                
            collapsible:            'mage/collapsible',
            dropdownDialog:         'mage/dropdown',
            dropdown:               'mage/dropdowns',
            accordion:              'mage/accordion',
            loader:                 'mage/loader',
            tooltip:                'mage/tooltip',
            deletableItem:          'mage/deletable-item',
            itemTable:              'mage/item-table',
            fieldsetControls:       'mage/fieldset-controls',
            fieldsetResetControl:   'mage/fieldset-controls',
            redirectUrl:            'mage/redirect-url',
            loaderAjax:             'mage/loader',
            menu:                   'mage/menu',
            popupWindow:            'mage/popup-window',
            validation:             'mage/validation/validation'
        }
    },
    paths: {
        'jquery/ui': 'jquery/jquery-ui'
    },
    deps: [
        'jquery',
        'jquery/jquery-migrate',
        'jquery/jquery.mobile.custom',
        'js/responsive',
        'mage/common',
        'mage/dataPost',
        'js/theme',
        'mage/terms',
        'mage/bootstrap'
    ]
};
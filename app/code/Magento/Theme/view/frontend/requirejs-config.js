/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
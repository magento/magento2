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

define([], function() {
    var components = {
        categoryForm: 'Magento_Catalog/catalog/category/form',
        newCategoryDialog: 'Magento_Catalog/js/new-category-dialog',
        requireCookie: 'Magento_Core/js/require-cookie',

        addressTabs: 'Magento_Customer/edit/tab/js/addresses',
        dataItemDeleteButton: 'Magento_Customer/edit/tab/js/addresses',
        groupedProduct: 'Magento_GroupedProduct/js/grouped-product',
        observableInputs: 'Magento_Customer/edit/tab/js/addresses',
        translateInline: 'mage/translate-inline',

        //Backend\view\adminhtml\templates\page\js\components.phtml
        form: 'mage/backend/form',
        button: 'mage/backend/button',
        accordion: 'mage/accordion',
        actionLink: 'mage/backend/action-link',
        validation: 'mage/backend/validation',
        notification: 'mage/backend/notification',
        loader: 'mage/loader_old',
        loaderAjax: 'mage/loader_old',
        floatingHeader: 'mage/backend/floating-header',
        suggest: 'mage/backend/suggest',
        mediabrowser: 'jquery/jstree/jquery.jstree',
        rolesTree: 'Magento_User/js/roles-tree',
        folderTree: 'Magento_Cms/js/folder-tree',
        categoryTree: 'Magento_Catalog/js/category-tree',
        tabs: 'mage/backend/tabs',
        treeSuggest: 'mage/backend/tree-suggest',
        baseImage: 'baseImage',

        //DesignEditor\view\adminhtml\templates\editor\toolbar\buttons\edit
        'vde-edit-button': 'Magento_DesignEditor/js/theme-revert',

        //Sales\view\adminhtml\templates\page\js\components.phtml
        orderEditDialog: 'Magento_Sales/order/edit/message',

        variationsAttributes: 'Magento_ConfigurableProduct/catalog/product-variation',
        calendar: 'mage/calendar',
        productGallery: 'Magento_Catalog/js/product-gallery',
        configurableAttribute: 'Magento_ConfigurableProduct/catalog/product/attribute',
        systemMessageDialog: 'Magento_AdminNotification/system/notification',
        fptAttribute: 'Magento_Weee/js/fpt-attribute',
        dropdown: 'mage/dropdown_old',
        collapsable: 'js/theme',
        collapsible: 'mage/collapsible',
        menu: 'mage/backend/menu',
        themeEdit: 'Magento_DesignEditor/js/theme-edit',
        integration: 'Magento_Integration/js/integration'
    };

    return components;
});
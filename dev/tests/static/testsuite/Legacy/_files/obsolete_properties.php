<?php
/**
 * Obsolete class attributes
 *
 * Format: array(<attribute_name>[, <class_scope> = ''[, <replacement>]])
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    array('_addresses', 'Mage_Customer_Model_Customer'),
    array('_addMinimalPrice', 'Mage_Catalog_Model_Resource_Product_Collection'),
    array('_alias', 'Mage_Core_Block_Abstract'),
    array('_anonSuffix'),
    array('_baseDirCache', 'Mage_Core_Model_Config'),
    array('_canUseLocalModules'),
    array('_checkedProductsQty', 'Mage_CatalogInventory_Model_Observer'),
    array('_children', 'Mage_Core_Block_Abstract'),
    array('_childrenHtmlCache', 'Mage_Core_Block_Abstract'),
    array('_childGroups', 'Mage_Core_Block_Abstract'),
    array('_combineHistory'),
    array('_config', 'Mage_Core_Model_Design_Package'),
    array('_config', 'Mage_Core_Model_Logger', '_dirs'),
    array('_configuration', 'Mage_Index_Model_Lock_Storage', '_dirs'),
    array('_currency', 'Mage_GoogleCheckout_Model_Api_Xml_Checkout'),
    array('_currencyNameTable'),
    array('_customEtcDir', 'Mage_Core_Model_Config'),
    array('_designProductSettingsApplied'),
    array('_distroServerVars'),
    array('_entityIdsToIncrementIds'),
    array('_entityTypeIdsToTypes'),
    array('_isAnonymous'),
    array('_isFirstTimeProcessRun', 'Mage_SalesRule_Model_Validator'),
    array('_loadDefault', 'Mage_Core_Model_Resource_Store_Collection'),
    array('_loadDefault', 'Mage_Core_Model_Resource_Store_Group_Collection'),
    array('_loadDefault', 'Mage_Core_Model_Resource_Website_Collection'),
    array('_option', 'Mage_Captcha_Helper_Data', '_dirs'),
    array('_options', 'Mage_Core_Model_Config', 'Mage_Core_Model_Dir'),
    array('_optionsMapping', null, 'Mage::getBaseDir($nodeKey)'),
    array('_order', 'Mage_Checkout_Block_Onepage_Success'),
    array('_order_id'),
    array('_parent', 'Mage_Core_Block_Abstract'),
    array('_parentBlock', 'Mage_Core_Block_Abstract'),
    array('_persistentCustomerGroupId'),
    array('_ratingOptionTable', 'Mage_Rating_Model_Resource_Rating_Option_Collection'),
    array('_saveTemplateFlag', 'Mage_Newsletter_Model_Queue'),
    array('_searchTextFields'),
    array('_setAttributes', 'Mage_Catalog_Model_Product_Type_Abstract'),
    array('_skipFieldsByModel'),
    array('_ship_id'),
    array('_shipTable', 'Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection'),
    array('_sortedChildren'),
    array('_sortInstructions'),
    array('_storeFilter', 'Mage_Catalog_Model_Product_Type_Abstract'),
    array('_substServerVars'),
    array('_track_id'),
    array('_varSubFolders', null, 'Mage_Core_Model_Dir'),
    array('_viewDir', 'Mage_Core_Block_Template', '_dirs'),
    array('decoratedIsFirst', null, 'getDecoratedIsFirst'),
    array('decoratedIsEven', null, 'getDecoratedIsEven'),
    array('decoratedIsOdd', null, 'getDecoratedIsOdd'),
    array('decoratedIsLast', null, 'getDecoratedIsLast'),
    array('static', 'Mage_Core_Model_Email_Template_Filter'),
);

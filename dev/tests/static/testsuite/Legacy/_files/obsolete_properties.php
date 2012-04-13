<?php
/**
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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    '_anonSuffix',
    '_isAnonymous',
    'decoratedIsFirst' => array('suggestion' => 'getDecoratedIsFirst'),
    'decoratedIsEven' => array('suggestion' => 'getDecoratedIsEven'),
    'decoratedIsOdd' => array('suggestion' => 'getDecoratedIsOdd'),
    'decoratedIsLast' => array('suggestion' => 'getDecoratedIsLast'),
    '_alias' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_children' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_childrenHtmlCache' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_childGroups' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_currencyNameTable',
    '_combineHistory',
    '_searchTextFields',
    '_skipFieldsByModel',
    '_imageFields' => array('class_scope' => 'Mage_Catalog_Model_Convert_Adapter_Product'),
    '_parent' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_parentBlock' => array('class_scope' => 'Mage_Core_Block_Abstract'),
    '_setAttributes' => array('class_scope' => 'Mage_Catalog_Model_Product_Type_Abstract'),
    '_storeFilter' => array('class_scope' => 'Mage_Catalog_Model_Product_Type_Abstract'),
    '_addMinimalPrice' => array('class_scope' => 'Mage_Catalog_Model_Resource_Product_Collection'),
    '_checkedProductsQty' => array('class_scope' => 'Mage_CatalogInventory_Model_Observer'),
    '_baseDirCache' => array('class_scope' => 'Mage_Core_Model_Config'),
    '_customEtcDir' => array('class_scope' => 'Mage_Core_Model_Config'),
    'static' => array('class_scope' => 'Mage_Core_Model_Email_Template_Filter'),
    '_loadDefault' => array('class_scope' => 'Mage_Core_Model_Resource_Store_Collection'),
    '_loadDefault' => array('class_scope' => 'Mage_Core_Model_Resource_Store_Group_Collection'),
    '_loadDefault' => array('class_scope' => 'Mage_Core_Model_Resource_Website_Collection'),
    '_addresses' => array('class_scope' => 'Mage_Customer_Model_Customer'),
    '_currency' => array('class_scope' => 'Mage_GoogleCheckout_Model_Api_Xml_Checkout'),
    '_saveTemplateFlag' => array('class_scope' => 'Mage_Newsletter_Model_Queue'),
    '_ratingOptionTable' => array('class_scope' => 'Mage_Rating_Model_Resource_Rating_Option_Collection'),
    '_entityTypeIdsToTypes',
    '_entityIdsToIncrementIds',
    '_isFirstTimeProcessRun' => array('class_scope' => 'Mage_SalesRule_Model_Validator'),
    '_shipTable' => array('class_scope' => 'Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection'),
    '_designProductSettingsApplied',
    '_order' => array('class_scope' => 'Mage_Checkout_Block_Onepage_Success'),
    '_track_id',
    '_order_id',
    '_ship_id',
    '_sortedChildren',
    '_sortInstructions',
);

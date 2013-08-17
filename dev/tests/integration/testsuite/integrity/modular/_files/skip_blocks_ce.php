<?php
/**
 * List of blocks to be skipped from instantiation test
 *
 * Format: array('Block_Class_Name', ...)
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
    // Blocks with abstract constructor arguments
    'Mage_Adminhtml_Block_System_Email_Template',
    'Mage_Adminhtml_Block_System_Email_Template_Edit',
    'Mage_Backend_Block_System_Config_Edit',
    'Mage_Backend_Block_System_Config_Form',
    'Mage_Backend_Block_System_Config_Tabs',
    // Fails because of bug in Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader constructor
    'Mage_Adminhtml_Block_Cms_Page',
    'Mage_Adminhtml_Block_Cms_Page_Edit',
    'Mage_Adminhtml_Block_Sales_Order',
    'Mage_Oauth_Block_Adminhtml_Oauth_Consumer',
    'Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Grid',
    'Mage_Paypal_Block_Adminhtml_Settlement_Report',
    'Mage_Sales_Block_Adminhtml_Billing_Agreement_View',
    'Mage_User_Block_Role_Tab_Edit',
    'Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource',
);

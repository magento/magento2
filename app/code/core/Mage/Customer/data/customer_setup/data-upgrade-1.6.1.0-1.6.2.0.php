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
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$disableAGCAttribute = Mage::getSingleton('Mage_Eav_Model_Config')
    ->getAttribute('customer', 'disable_auto_group_change');
$disableAGCAttribute->setData('used_in_forms', array(
    'adminhtml_customer'
));
$disableAGCAttribute->save();

$vatAttribute = Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute('customer_address', 'vat_id');
$vatAttribute->setData('used_in_forms', array(
     'adminhtml_customer_address',
     'customer_address_edit',
     'customer_register_address'
));
$vatAttribute->save();

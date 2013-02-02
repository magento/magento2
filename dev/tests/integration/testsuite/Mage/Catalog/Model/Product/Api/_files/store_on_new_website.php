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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!Mage::registry('website')) {
    $website = Mage::getModel('Mage_Core_Model_Website');
    $website->setData(
        array(
            'code' => 'test_' . uniqid(),
            'name' => 'test website',
            'default_group_id' => 1,
        )
    );
    $website->save();
    Mage::register('website', $website);
}

if (!Mage::registry('store_group')) {
    $defaultCategoryId = 2;
    $storeGroup = Mage::getModel('Mage_Core_Model_Store_Group');
    $storeGroup->setData(
        array(
            'website_id' => Mage::registry('website')->getId(),
            'name' => 'Test Store' . uniqid(),
            'code' => 'store_group_' . uniqid(),
            'root_category_id' => $defaultCategoryId
        )
    )->save();
    Mage::register('store_group', $storeGroup);
}

if (!Mage::registry('store_on_new_website')) {
    $store = Mage::getModel('Mage_Core_Model_Store');
    $store->setData(
        array(
            'group_id' => Mage::registry('store_group')->getId(),
            'name' => 'Test Store View',
            'code' => 'store_' . uniqid(),
            'is_active' => true,
            'website_id' => Mage::registry('website')->getId()
        )
    )->save();
    Mage::register('store_on_new_website', $store);
    Mage::app()->reinitStores();
}

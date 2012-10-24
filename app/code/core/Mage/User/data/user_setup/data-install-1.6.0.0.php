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
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Save administrators group role and rules
 */

/** @var $roleCollection Mage_User_Model_Resource_Role_Collection */
$roleCollection = Mage::getModel('Mage_User_Model_Role')->getCollection()
    ->addFieldToFilter('parent_id', 0)
    ->addFieldToFilter('tree_level', 1)
    ->addFieldToFilter('role_type', 'G')
    ->addFieldToFilter('user_id', 0)
    ->addFieldToFilter('role_name', 'Administrators');

if ($roleCollection->count() == 0) {
    $admGroupRole = Mage::getModel('Mage_User_Model_Role')->setData(array(
        'parent_id'     => 0,
        'tree_level'    => 1,
        'sort_order'    => 1,
        'role_type'     => 'G',
        'user_id'       => 0,
        'role_name'     => 'Administrators'
    ))
    ->save();
} else {
    foreach ($roleCollection as $item) {
        $admGroupRole = $item;
        break;
    }
}

/** @var $rulesCollection Mage_User_Model_Resource_Rules_Collection */
$rulesCollection = Mage::getModel('Mage_User_Model_Rules')->getCollection()
    ->addFieldToFilter('role_id', $admGroupRole->getId())
    ->addFieldToFilter('resource_id', 'all')
    ->addFieldToFilter('role_type', 'G');

if ($rulesCollection->count() == 0) {
    Mage::getModel('Mage_User_Model_Rules')->setData(array(
        'role_id'       => $admGroupRole->getId(),
        'resource_id'   => 'Mage_Adminhtml::all',
        'privileges'    => null,
        'role_type'     => 'G',
        'permission'    => 'allow'
        ))
    ->save();
} else {
    foreach ($rulesCollection as $rule) {
        $rule->setData('resource_id', 'Mage_Adminhtml::all')
            ->save();
    }
}

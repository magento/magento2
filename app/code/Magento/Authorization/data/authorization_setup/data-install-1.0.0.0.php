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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Save administrators group role and rules
 */

/** @var \Magento\Authorization\Model\Resource\Setup $this */

$roleCollection = $this->createRoleCollection()
    ->addFieldToFilter('parent_id', 0)
    ->addFieldToFilter('tree_level', 1)
    ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
    ->addFieldToFilter('user_id', 0)
    ->addFieldToFilter('user_type', UserContextInterface::USER_TYPE_ADMIN)
    ->addFieldToFilter('role_name', 'Administrators');

if ($roleCollection->count() == 0) {
    $admGroupRole = $this->createRole()->setData(
        array(
            'parent_id' => 0,
            'tree_level' => 1,
            'sort_order' => 1,
            'role_type' => RoleGroup::ROLE_TYPE,
            'user_id' => 0,
            'user_type' => UserContextInterface::USER_TYPE_ADMIN,
            'role_name' => 'Administrators'
        )
    )->save();
} else {
    foreach ($roleCollection as $item) {
        $admGroupRole = $item;
        break;
    }
}

$rulesCollection = $this->createRulesCollection()
    ->addFieldToFilter('role_id', $admGroupRole->getId())
    ->addFieldToFilter('resource_id', 'all');

if ($rulesCollection->count() == 0) {
    $this->createRules()->setData(
        array(
            'role_id' => $admGroupRole->getId(),
            'resource_id' => 'Magento_Adminhtml::all',
            'privileges' => null,
            'permission' => 'allow'
        )
    )->save();
} else {
    /** @var \Magento\Authorization\Model\Rules $rule */
    foreach ($rulesCollection as $rule) {
        $rule->setData('resource_id', 'Magento_Adminhtml::all')->save();
    }
}

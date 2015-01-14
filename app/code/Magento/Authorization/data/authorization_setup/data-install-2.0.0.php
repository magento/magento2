<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Save administrators group role and rules
 */

/** @var $this \Magento\Authorization\Model\Resource\Setup */

$roleCollection = $this->createRoleCollection()
    ->addFieldToFilter('parent_id', 0)
    ->addFieldToFilter('tree_level', 1)
    ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
    ->addFieldToFilter('user_id', 0)
    ->addFieldToFilter('user_type', UserContextInterface::USER_TYPE_ADMIN)
    ->addFieldToFilter('role_name', 'Administrators');

if ($roleCollection->count() == 0) {
    $admGroupRole = $this->createRole()->setData(
        [
            'parent_id' => 0,
            'tree_level' => 1,
            'sort_order' => 1,
            'role_type' => RoleGroup::ROLE_TYPE,
            'user_id' => 0,
            'user_type' => UserContextInterface::USER_TYPE_ADMIN,
            'role_name' => 'Administrators',
        ]
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
        [
            'role_id' => $admGroupRole->getId(),
            'resource_id' => 'Magento_Adminhtml::all',
            'privileges' => null,
            'permission' => 'allow',
        ]
    )->save();
} else {
    /** @var \Magento\Authorization\Model\Rules $rule */
    foreach ($rulesCollection as $rule) {
        $rule->setData('resource_id', 'Magento_Adminhtml::all')->save();
    }
}

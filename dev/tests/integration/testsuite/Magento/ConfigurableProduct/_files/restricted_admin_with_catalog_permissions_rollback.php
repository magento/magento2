<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

// Deleting the user and the role.
/** @var User $user */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->loadByUsername('admincatalog_user')->delete();
/** @var Role $role */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->load('role_catalog_permissions', 'role_name');
if ($role->getId()) {
    /** @var Rules $rules */
    $rules = Bootstrap::getObjectManager()->get(RulesFactory::class)->create();
    $rules->load($role->getId(), 'role_id');
    $rules->delete();
    $role->delete();
}

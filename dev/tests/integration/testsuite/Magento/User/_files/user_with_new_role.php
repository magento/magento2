<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Authorization\Model\Role;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

$objectManager = Bootstrap::getObjectManager();
/** @var Role $role */
$role = $objectManager->create(Role::class);
$role->load('new_role', 'role_name');
if (!$role->getId()) {
    $role->setRoleName('new_role');
    $role->setRoleType('G');
    $role->save();
}

/** @var User $model */
$model = $objectManager->create(User::class);
$model->load('admin_with_role', 'username');
if (!$model->getId()) {
    $model->setFirstname("John")
        ->setLastname("Doe")
        ->setUsername('admin_with_role')
        ->setPassword('12345abc')
        ->setEmail('admin_with_role@example.com')
        ->setRoleType('G')
        ->setResourceId('Magento_Backend::all')
        ->setPrivileges("")
        ->setAssertId(0)
        ->setRoleId($role->getId())
        ->setPermission('allow');
    $model->save();
}

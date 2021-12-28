<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Role;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\UserFactory;
use Magento\User\Model\User;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\Rules;

//Deleting the user and the role.
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var User $user */
$user = $objectManager->create(UserFactory::class)->create();
$user->load('customRoleUser', 'username');

if ($user->getId() !== null) {
    $user->delete();
}

/** @var Role $role */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->load('test_custom_role', 'role_name');
/** @var Rules $rules */
$rules = Bootstrap::getObjectManager()->get(RulesFactory::class)->create();
$rules->load($role->getId(), 'role_id');

if ($rules->getId() !== null) {
    $rules->delete();
}
if ($role->getId() !== null) {
    $role->delete();
}

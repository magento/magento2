<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/** @var Role $role */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->setName('role_catalog_permissions');
$role->setData('role_name', $role->getName());
$role->setRoleType(Group::ROLE_TYPE);
$role->setUserType((string)UserContextInterface::USER_TYPE_ADMIN);
$role->save();

/** @var $rule Rules */
$rule = Bootstrap::getObjectManager()->create(Rules::class);
$rule->setRoleId($role->getId())->setResources(['Magento_Catalog::catalog'])->saveRel();

/** @var User $user */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->setData(
    [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'email' => 'admincatalog@example.com',
        'username' => 'admincatalog_user',
        'password' => 'admincatalog_password1',
        'is_active' => 1,
    ]
);
$user->setRoleId($role->getId())->save();

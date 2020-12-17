<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Role;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\User\Model\ResourceModel\User as UserResource;

//Creating a new admin user with a custom role to safely change role settings without affecting the main user's role.
/** @var Role $role */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->setName('test_custom_role');
$role->setData('role_name', $role->getName());
$role->setRoleType(\Magento\Authorization\Model\Acl\Role\Group::ROLE_TYPE);
$role->setUserType((string)\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);

/** @var RoleResource $roleResource */
$roleResource = Bootstrap::getObjectManager()->get(RoleResource::class);
$roleResource->save($role);

/** @var Rules $rules */
$rules = Bootstrap::getObjectManager()->get(RulesFactory::class)->create();
$rules->setRoleId($role->getId());
//Granted all permissions.
$rules->setResources([Bootstrap::getObjectManager()->get(\Magento\Framework\Acl\RootResource::class)->getId()]);

/** @var RulesResource $rulesResource */
$rulesResource = Bootstrap::getObjectManager()->get(RulesResource::class);
$rulesResource->saveRel($rules);

/** @var User $user */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('TestAdmin1')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('testadmin1@gmail.com')
    ->setIsActive(1)
    ->setRoleId($role->getId());

/** @var UserResource $userResource */
$userResource = Bootstrap::getObjectManager()->get(UserResource::class);
$userResource->save($user);
<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\UserContextInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User as UserResource;

/**
 * Create admin role with needed ACL
 */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->setName('login_as_customer_api_role');
$role->setData('role_name', $role->getName());
$role->setRoleType(Group::ROLE_TYPE);
$role->setUserType((string)UserContextInterface::USER_TYPE_ADMIN);

/** @var RoleResource $roleResource */
$roleResource = Bootstrap::getObjectManager()->get(RoleResource::class);
$roleResource->save($role);

/**
 * ACL rule: allow access to API token generation
 */
$rules = Bootstrap::getObjectManager()->get(RulesFactory::class)->create();
$rules->setRoleId($role->getId());
$rules->setResources(['Magento_LoginAsCustomerApi::token']);

/** @var RulesResource $rulesResource */
$rulesResource = Bootstrap::getObjectManager()->get(RulesResource::class);
$rulesResource->saveRel($rules);

/**
 * Create admin user
 */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->setUsername('TestAdminLoginAsCustomer')
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setEmail('testAdminLoginAsCustomer@example.com')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setIsActive(1)
    ->setRoleId($role->getId());

/** @var UserResource $userResource */
$userResource = Bootstrap::getObjectManager()->get(UserResource::class);
$userResource->save($user);

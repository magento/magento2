<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
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

//Deleting the user and the role.
/** @var User $user */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->load('TestAdminLoginAsCustomer', 'username');

/** @var UserResource $userResource */
$userResource = Bootstrap::getObjectManager()->get(UserResource::class);
$userResource->delete($user);

/** @var Role $role */
$role = Bootstrap::getObjectManager()->get(RoleFactory::class)->create();
$role->load('login_as_customer_api_role', 'role_name');


/** @var Rules $rules */
$rules = Bootstrap::getObjectManager()->get(RulesFactory::class)->create();
$rules->load($role->getId(), 'role_id');

/** @var RulesResource $rulesResource */
$rulesResource = Bootstrap::getObjectManager()->get(RulesResource::class);
$rulesResource->delete($rules);

/** @var RoleResource $roleResource */
$roleResource = Bootstrap::getObjectManager()->get(RoleResource::class);
$roleResource->delete($role);

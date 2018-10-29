<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/**
 * Create an admin user with an assigned role
 */

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\User\Model\ResourceModel\User $model */
$userResource = $objectManager->create(\Magento\User\Model\ResourceModel\User::class);

/** @var $user User */
$user = $objectManager->create(User::class);
$user->setFirstname("John")
    ->setIsActive(true)
    ->setLastname("Doe")
    ->setUsername('johnAdmin')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('JohnadminUser@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Backend::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');

$userResource->save($user);

/** @var $user User */
$user = $objectManager->create(User::class);
$user->setFirstname("Ann")
    ->setIsActive(true)
    ->setLastname("Doe")
    ->setUsername('annAdmin')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('JaneadminUser@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Backend::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');

$userResource->save($user);

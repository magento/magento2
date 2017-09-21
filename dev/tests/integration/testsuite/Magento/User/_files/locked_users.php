<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * Create an admin user with an assigned role
 */
$userIds = [];

/** @var $model \Magento\User\Model\User */
$model = $objectManager->create(\Magento\User\Model\User::class);
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser1')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser1@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Adminhtml::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();
$userIds[] = $model->getDataByKey('user_id');

/** @var $model \Magento\User\Model\User */
$model = $objectManager->create(\Magento\User\Model\User::class);
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser2')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser2@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Adminhtml::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();
$userIds[] = $model->getDataByKey('user_id');

$defaultAdminUserId = 1;
$lockLifetime = 86400;

/** @var $modelLockedUsers \Magento\User\Model\ResourceModel\User */
$modelLockedUsers = $objectManager->create(\Magento\User\Model\ResourceModel\User::class);
$modelLockedUsers->lock($userIds, $defaultAdminUserId, $lockLifetime);

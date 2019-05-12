<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * Create an admin user with expired access date
 */
$userIds = [];

/** @var $model \Magento\User\Model\User */
$model = $objectManager->create(\Magento\User\Model\User::class);
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser3')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser3@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Adminhtml::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();
$userIds[] = $model->getDataByKey('user_id');

/** @var $model \Magento\User\Model\User */
$futureDate = new \DateTime();
$futureDate->modify('+10 days');
$model = $objectManager->create(\Magento\User\Model\User::class);
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser4')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser4@example.com')
    ->setExpiresAt($futureDate->format('Y-m-d H:i:s'))
    ->setRoleType('G')
    ->setResourceId('Magento_Adminhtml::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();
$userIds[] = $model->getDataByKey('user_id');

// need to bypass model validation to set expired date
$resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
$conn = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
$tableName = $resource->getTableName('admin_user');
$sql = "UPDATE " . $tableName . " SET expires_at = '2010-01-01 00:00:00' WHERE user_id=" .
    $userIds[0] . ";";
$result = $conn->query($sql);



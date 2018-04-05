<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Create an admin user with an assigned role
 */

/** @var $model \Magento\User\Model\User */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
/** @var Magento\Framework\App\ResourceConnection $connection */
$connection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(Magento\Framework\App\ResourceConnection::class);
$adapter = $connection->getConnection();
$select = $adapter->select()
    ->from('authorization_role', ['role_id'])
    ->where('role_name = ?', 'Administrators')
    ->where('parent_id = ?', 0)
    ->limit(1);
$roleId = $adapter->fetchOne($select);
$model->setFirstname("Web")
    ->setLastname("Api")
    ->setUsername('webapi_user')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('webapi_user@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Backend::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId((int) $roleId)
    ->setPermission('allow');
$model->save();

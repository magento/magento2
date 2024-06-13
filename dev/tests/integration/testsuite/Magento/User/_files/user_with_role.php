<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Create an admin user with an assigned role
 */

/** @var $model \Magento\User\Model\User */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Backend::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();

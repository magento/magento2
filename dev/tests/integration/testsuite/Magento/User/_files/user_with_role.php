<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Create an admin user with an assigned role
 */

/** @var $model \Magento\User\Model\User */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
$model->setFirstname("John")
    ->setLastname("Doe")
    ->setUsername('adminUser')
    ->setPassword(\Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
    ->setEmail('adminUser@example.com')
    ->setRoleType('G')
    ->setResourceId('Magento_Adminhtml::all')
    ->setPrivileges("")
    ->setAssertId(0)
    ->setRoleId(1)
    ->setPermission('allow');
$model->save();

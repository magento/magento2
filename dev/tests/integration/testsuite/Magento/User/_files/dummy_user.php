<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Create dummy user
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()
    ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
$user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
$user->setFirstname(
    'Dummy'
)->setLastname(
    'Dummy'
)->setEmail(
    'dummy@dummy.com'
)->setUsername(
    'dummy_username'
)->setPassword(
    'dummy_password1'
)->save();

\Magento\TestFramework\Helper\Bootstrap::getInstance()
    ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
$user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
$user->setFirstname(
    'CreateDate'
)->setLastname(
    'User 2'
)->setEmail(
    'dummy2@dummy.com'
)->setUsername(
    'user_created_date'
)->setPassword(
    'dummy_password2'
)->save();
$user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\User\Model\User');
$user->loadByUsername('user_created_date');
$user->setCreated('2010-01-06 00:00:00');
$user->save();

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

$om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var PasswordResetRequestEvent $passwordResetRequestEvent */
$passwordResetRequestEvent = $om->create(\Magento\Security\Model\PasswordResetRequestEvent::class);

$passwordResetRequestEvent
    ->setRequestType(PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST)
    ->setAccountReference('test27.dev@gmail.com')
    ->setCreatedAt('2016-01-20 13:00:13')
    ->setIp('3232249856')
    ->save();

$passwordResetRequestEvent = $om->create(\Magento\Security\Model\PasswordResetRequestEvent::class);
$passwordResetRequestEvent
    ->setRequestType(PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST)
    ->setAccountReference('test273.dev@gmail.com')
    ->setCreatedAt('2016-01-19 13:00:13')
    ->setIp('3232249857')
    ->save();

$passwordResetRequestEvent = $om->create(\Magento\Security\Model\PasswordResetRequestEvent::class);
$passwordResetRequestEvent
    ->setRequestType(PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST)
    ->setAccountReference('test2745.dev@gmail.com')
    ->setCreatedAt('2016-01-18 13:00:13')
    ->setIp('3232249858')
    ->save();

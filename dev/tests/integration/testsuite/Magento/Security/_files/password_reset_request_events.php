<?php

namespace Magento\Security\Model;

$om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var PasswordResetRequestEvent $passwordResetRequestEvent */
$passwordResetRequestEvent = $om->create('Magento\Security\Model\PasswordResetRequestEvent');

$passwordResetRequestEvent
    ->setId(1)
    ->setRequestType(PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST)
    ->setAccountReference('1')
    ->setCreatedAt('2016-01-20 13:00:13')
    ->setIp('0')
    ->save();


$passwordResetRequestEvent
    ->setId(2)
    ->setRequestType(PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST)
    ->setAccountReference('1')
    ->setCreatedAt('2016-01-19 13:00:13')
    ->setIp('0')
    ->save();

$passwordResetRequestEvent
    ->setId(3)
    ->setRequestType(PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST)
    ->setAccountReference('1')
    ->setCreatedAt('2016-01-18 13:00:13')
    ->setIp('0')
    ->save();


<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 1'
)->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(\Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 1')
    ->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 2'
)->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Unread Critical 3'
)->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Read Critical 1'
)->setIsRead(
    1
)->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(\Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 2')
    ->save();

$message = $om->create('Magento\AdminNotification\Model\Inbox');
$message->setSeverity(
    \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL
)->setTitle(
    'Removed Critical 1'
)->setIsRemove(
    1
)->save();

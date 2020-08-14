<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\AdminNotification\Model\Inbox;
use Magento\Framework\Notification\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

$om = Bootstrap::getObjectManager();
$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_CRITICAL)
    ->setTitle('Unread Critical 1')
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 1')
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_CRITICAL)
    ->setTitle('Unread Critical 2')
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_CRITICAL)
    ->setTitle('Unread Critical 3')
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_CRITICAL)
    ->setTitle('Read Critical 1')
    ->setIsRead(1)
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_MAJOR)
    ->setTitle('Unread Major 2')
    ->save();

$message = $om->create(Inbox::class);
$message
    ->setSeverity(MessageInterface::SEVERITY_CRITICAL)
    ->setTitle('Removed Critical 1')
    ->setIsRemove(1)
    ->save();

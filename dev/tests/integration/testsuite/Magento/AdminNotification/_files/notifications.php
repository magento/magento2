<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\AdminNotification\Model\Inbox;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/**
 * @var Inbox $message
 * @var InboxResource $messageResource
 */
$message = $objectManager->create(Inbox::class);
$messageResource = $objectManager->create(InboxResource::class);

$message->setSeverity(MessageInterface::SEVERITY_CRITICAL)->setTitle('Unread Critical 1');
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_MAJOR)->setTitle('Unread Major 1');
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_CRITICAL)->setTitle('Unread Critical 2');
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_CRITICAL)->setTitle('Unread Critical 3');
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_CRITICAL)->setTitle('Read Critical 1')->setIsRead(1);
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_MAJOR)->setTitle('Unread Major 2');
$messageResource->save($message);

$message = $objectManager->create(Inbox::class);
$message->setSeverity(MessageInterface::SEVERITY_CRITICAL)->setTitle('Removed Critical 1')->setIsRemove(1);
$messageResource->save($message);

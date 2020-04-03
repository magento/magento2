<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Stdlib\DateTime;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';

$objectManager = Bootstrap::getObjectManager();
/** @var PasswordResetRequestEvent $passwordResetRequestEventResource */
$passwordResetRequestEventResource = $objectManager->get(PasswordResetRequestEvent::class);
$dateTime = new DateTimeImmutable();
$passwordResetRequestEventResource->deleteRecordsOlderThen($dateTime->format(DateTime::DATETIME_PHP_FORMAT));

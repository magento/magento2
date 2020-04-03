<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Framework\Stdlib\DateTime;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var CustomerAuthUpdate $customerAuthUpdate */
$customerAuthUpdate = $objectManager->get(CustomerAuthUpdate::class);

$customerSecure = $customerRegistry->retrieveSecureData($customer->getId());
$dateTime = new \DateTimeImmutable();
$customerSecure->setFailuresNum(10)
    ->setFirstFailure($dateTime->modify('-15 minutes')->format(DateTime::DATETIME_PHP_FORMAT))
    ->setLockExpires($dateTime->modify('-5 minutes')->format(DateTime::DATETIME_PHP_FORMAT));
$customerAuthUpdate->saveAuth($customer->getId());
$customerRegistry->remove($customer->getId());

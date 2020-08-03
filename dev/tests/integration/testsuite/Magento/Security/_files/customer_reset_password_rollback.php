<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Manager $moduleManager */
$moduleManager = $objectManager->get(Manager::class);
//This check is needed because Magento_Security independent of Magento_Customer
if ($moduleManager->isEnabled('Magento_Customer')) {
    require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';

    /** @var PasswordResetRequestEvent $passwordResetRequestEventResource */
    $passwordResetRequestEventResource = $objectManager->get(PasswordResetRequestEvent::class);
    $dateTime = new DateTimeImmutable();
    $passwordResetRequestEventResource->deleteRecordsOlderThen($dateTime->format(DateTime::DATETIME_PHP_FORMAT));
}
